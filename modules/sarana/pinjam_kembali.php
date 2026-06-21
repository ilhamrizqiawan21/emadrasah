<?php
// emadrasah/modules/sarana/pinjam_kembali.php
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
        // Cek apakah memang benar dipinjam
        $stmt_check = $pdo->prepare("SELECT status FROM peminjaman_sarana WHERE id = ?");
        $stmt_check->execute([$id]);
        $status = $stmt_check->fetchColumn();

        if ($status === 'dipinjam') {
            $stmt = $pdo->prepare("UPDATE peminjaman_sarana SET status = 'kembali', tanggal_kembali = CURDATE(), updated_at = NOW() WHERE id = ?");
            $stmt->execute([$id]);

            // Tambahkan stok_tersedia
            $pdo->prepare("UPDATE sarana_prasarana SET stok_tersedia = stok_tersedia + 1 WHERE id = ?")->execute([$sarana_id]);

            set_flash('success', 'Barang telah dikembalikan.');
        } else {
            set_flash('warning', 'Barang sudah dikembalikan sebelumnya.');
        }

        header("Location: pinjam.php?sarana_id=$sarana_id");
        exit;
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}
