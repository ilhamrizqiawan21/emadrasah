<?php
// emadrasah/modules/sarana/servis_selesai.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validate_csrf_request()) {
    set_flash('danger', 'Token CSRF tidak valid atau metode tidak diizinkan.');
    header('Location: ' . base_url('modules/sarana/index.php'));
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$sarana_id = isset($_POST['sarana_id']) ? (int)$_POST['sarana_id'] : 0;

if ($id) {
    try {
        // Cek apakah memang benar sedang servis
        $stmt_check = $pdo->prepare("SELECT status FROM pemeliharaan_sarana WHERE id = ?");
        $stmt_check->execute([$id]);
        $status = $stmt_check->fetchColumn();

        if ($status === 'proses') {
            $stmt = $pdo->prepare("UPDATE pemeliharaan_sarana SET status = 'selesai', tanggal_selesai = CURDATE(), updated_at = NOW() WHERE id = ?");
            $stmt->execute([$id]);

            // Tambahkan kembali ke stok_tersedia
            $pdo->prepare("UPDATE sarana_prasarana SET stok_tersedia = stok_tersedia + 1 WHERE id = ?")->execute([$sarana_id]);

            set_flash('success', 'Pemeliharaan selesai, barang kembali ke stok tersedia.');
        } else {
            set_flash('warning', 'Catatan pemeliharaan sudah selesai sebelumnya.');
        }

        header("Location: servis.php?sarana_id=$sarana_id");
        exit;
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}
