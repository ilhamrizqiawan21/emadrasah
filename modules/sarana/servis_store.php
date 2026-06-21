<?php
// emadrasah/modules/sarana/servis_store.php
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
    $tgl = $_POST['tanggal_pemeliharaan'];
    $teknisi = input_safe($_POST['teknisi']);
    $ket = input_safe($_POST['keterangan']);

    try {
        // Cek stok tersedia
        $stmt_stok = $pdo->prepare("SELECT stok_tersedia FROM sarana_prasarana WHERE id = ?");
        $stmt_stok->execute([$sarana_id]);
        $stok = $stmt_stok->fetchColumn();

        if ($stok <= 0) {
            set_flash('danger', 'Maaf, stok barang sedang kosong/habis tidak bisa diservis.');
            header("Location: servis.php?sarana_id=$sarana_id");
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO pemeliharaan_sarana (sarana_id, tanggal_pemeliharaan, teknisi, keterangan, status, created_at, updated_at) VALUES (?, ?, ?, ?, 'proses', NOW(), NOW())");
        $stmt->execute([$sarana_id, $tgl, $teknisi, $ket]);

        // Kurangi stok_tersedia karena barang ditarik untuk servis
        $pdo->prepare("UPDATE sarana_prasarana SET stok_tersedia = stok_tersedia - 1 WHERE id = ?")->execute([$sarana_id]);

        set_flash('success', 'Catatan pemeliharaan berhasil disimpan dan stok disesuaikan.');
        header("Location: servis.php?sarana_id=$sarana_id");
        exit;
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}
?>