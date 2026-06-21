<?php
// emadrasah/modules/kategori_sarana/update.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!validate_csrf_request()) {
    set_flash('danger', 'Token CSRF tidak valid.');
    $redirect = isset($_POST['id']) ? 'edit.php?id=' . (int)$_POST['id'] : 'index.php';
    header('Location: ' . $redirect);
    exit;
}

$id = (int)$_POST['id'];
try {
    $nama = input_safe($_POST['nama_kategori']);
    $desc = input_safe($_POST['deskripsi']);

    $stmt = $pdo->prepare("UPDATE kategori_sarana SET nama_kategori = ?, deskripsi = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$nama, $desc, $id]);

    set_flash('success', 'Kategori sarana berhasil diperbarui.');
    header('Location: index.php');
    exit;
} catch (Exception $e) {
    set_flash('danger', 'Terjadi kesalahan sistem: ' . $e->getMessage());
    header('Location: edit.php?id=' . $id);
    exit;
}
?>