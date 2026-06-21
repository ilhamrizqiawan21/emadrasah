<?php
// emadrasah/modules/jadwal/save_grid.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Metode tidak diperbolehkan.']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Sesi berakhir, silakan login kembali.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['changes'])) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid.']);
    exit;
}

$semester = isset($data['semester']) ? (int)$data['semester'] : 1;
$tahun_ajaran = isset($data['tahun_ajaran']) ? trim($data['tahun_ajaran']) : '';

if (empty($tahun_ajaran)) {
    echo json_encode(['success' => false, 'message' => 'Tahun ajaran tidak boleh kosong.']);
    exit;
}

// Support CSRF from JSON body
if (isset($data['csrf_token'])) {
    $_POST['csrf_token'] = $data['csrf_token'];
}

if (!validate_csrf_request()) {
    echo json_encode(['success' => false, 'message' => 'Token CSRF tidak valid.']);
    exit;
}

// Menggunakan Database Transaction untuk memastikan data konsisten
try {
    $pdo->beginTransaction();

    foreach ($data['changes'] as $change) {
        // Langkah A: Hapus data lama pada koordinat Kelas/Hari/Jam yang sama
        // Ini memungkinkan sistem melakukan 'Sync' (Update via Delete & Insert)
        $stmtDel = $pdo->prepare("DELETE FROM jadwals WHERE kelas_id = ? AND hari = ? AND jam_mulai = ? AND jam_selesai = ? AND semester = ? AND tahun_ajaran = ?");
        $stmtDel->execute([$change['kelas_id'], $change['hari'], $change['jam_mulai'], $change['jam_selesai'], $semester, $tahun_ajaran]);

        if (!empty($change['guru_ids'])) {
            foreach ($change['guru_ids'] as $gId) {
                if (empty($gId)) continue;
                
                // Langkah B: Otomatisasi Mapel
                // Mencari ID Mata Pelajaran yang namanya mirip dengan bidang studi guru
                $stmtM = $pdo->prepare("
                    SELECT m.id FROM mapels m 
                    JOIN gurus g ON LOWER(m.nama_mapel) LIKE CONCAT('%', LOWER(g.bidang_studi), '%') 
                    WHERE g.id = ? LIMIT 1
                ");
                $stmtM->execute([$gId]);
                $mapelId = $stmtM->fetchColumn();
                
                // Fallback jika tidak ditemukan
                if (!$mapelId) {
                    $mapelId = $pdo->query("SELECT id FROM mapels LIMIT 1")->fetchColumn();
                }

                // Langkah C: Insert data baru
                $stmtIns = $pdo->prepare("INSERT INTO jadwals (kelas_id, guru_id, mapel_id, hari, jam_mulai, jam_selesai, semester, tahun_ajaran, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
                $stmtIns->execute([
                    $change['kelas_id'],
                    $gId,
                    $mapelId,
                    $change['hari'],
                    $change['jam_mulai'],
                    $change['jam_selesai'],
                    $semester,
                    $tahun_ajaran
                ]);
            }
        }
    }

    $pdo->commit();
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>