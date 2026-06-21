<?php
// emadrasah/modules/sarana/pinjam_store.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
if (!validate_csrf_request()) {
    set_flash('danger', 'Token CSRF tidak valid.');
    $redirect = 'index.php';
    if (basename($_SERVER['PHP_SELF']) == 'store.php') $redirect = 'create.php';
    if (basename($_SERVER['PHP_SELF']) == 'update.php' && isset($_POST['id'])) $redirect = 'edit.php?id=' . $_POST['id'];
    header('Location: ' . $redirect);
    exit;
}

    $sarana_id = $_POST['sarana_id'];
    $peminjam = input_safe($_POST['peminjam']);
    $tipe = $_POST['tipe_peminjam'];
    $tgl_pinjam = $_POST['tanggal_pinjam'];

    try {
        $pdo->beginTransaction();

        // Cek stok tersedia
        $stmt_stok = $pdo->prepare("SELECT stok_tersedia FROM sarana_prasarana WHERE id = ?");
        $stmt_stok->execute([$sarana_id]);
        $stok = $stmt_stok->fetchColumn();

        if ($stok <= 0) {
            set_flash('danger', 'Maaf, stok barang sedang kosong/habis.');
            header("Location: " . base_url("modules/sarana/pinjam.php?sarana_id=$sarana_id"));
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO peminjaman_sarana (sarana_id, peminjam, tipe_peminjam, tanggal_pinjam, status, created_at, updated_at) VALUES (?, ?, ?, ?, 'dipinjam', NOW(), NOW())");
        $stmt->execute([$sarana_id, $peminjam, $tipe, $tgl_pinjam]);

        // Kurangi stok_tersedia
        $stmt_update = $pdo->prepare("UPDATE sarana_prasarana SET stok_tersedia = stok_tersedia - 1 WHERE id = ? AND stok_tersedia > 0");
        $stmt_update->execute([$sarana_id]);
        
        if ($stmt_update->rowCount() === 0) {
            throw new Exception("Stok tiba-tiba habis.");
        }

        $pdo->commit();
        set_flash('success', 'Peminjaman berhasil dicatat.');
        header("Location: " . base_url("modules/sarana/pinjam.php?sarana_id=$sarana_id"));
        exit;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        set_flash('danger', 'Terjadi kesalahan sistem.');
        header("Location: " . base_url("modules/sarana/pinjam.php?sarana_id=$sarana_id"));
    }
}
?>