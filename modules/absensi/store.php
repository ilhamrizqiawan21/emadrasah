<?php
// emadrasah/modules/absensi/store.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
if (!validate_csrf_request()) {
    set_flash('danger', 'Token CSRF tidak valid.');
    header('Location: ' . base_url('modules/absensi/index.php?tanggal=' . $_POST['tanggal']));
    exit;
}

    $tanggal = $_POST['tanggal'];
    
    try {
        $pdo->beginTransaction();

        foreach ($_POST['status'] as $guru_id => $status) {
            $keterangan = $_POST['keterangan'][$guru_id] ?? null;

            // Cek apakah data absensi hari ini sudah ada
            $stmt_cek = $pdo->prepare("SELECT id FROM agenda_guru WHERE tanggal = ? AND guru_id = ?");
            $stmt_cek->execute([$tanggal, $guru_id]);
            $exists = $stmt_cek->fetch();

            if ($exists) {
                // Update
                $stmt_upd = $pdo->prepare("UPDATE agenda_guru SET status = ?, keterangan = ?, updated_at = NOW() WHERE id = ?");
                $stmt_upd->execute([$status, $keterangan, $exists['id']]);
            } else {
                // Insert Baru
                $stmt_ins = $pdo->prepare("INSERT INTO agenda_guru (tanggal, guru_id, status, keterangan, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
                $stmt_ins->execute([$tanggal, $guru_id, $status, $keterangan]);
            }
        }

        $pdo->commit();
        set_flash('success', 'Data absensi guru berhasil diperbarui.');
        header("Location: index.php?tanggal=$tanggal");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
} else {
    header('Location: index.php');
}
?>