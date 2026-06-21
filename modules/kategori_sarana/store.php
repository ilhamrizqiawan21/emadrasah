<?php
// emadrasah/modules/kategori_sarana/store.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!validate_csrf_request()) {
    set_flash('danger', 'Token CSRF tidak valid.');
    header('Location: create.php');
    exit;
}

try {
    $nama = input_safe($_POST['nama_kategori']);
    $desc = input_safe($_POST['deskripsi']);

    $stmt = $pdo->prepare("INSERT INTO kategori_sarana (nama_kategori, deskripsi, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
    $stmt->execute([$nama, $desc]);

    set_flash('success', 'Kategori sarana berhasil ditambahkan.');
    header('Location: index.php');
    exit;
} catch (Exception $e) {
    set_flash('danger', 'Terjadi kesalahan sistem: ' . $e->getMessage());
    header('Location: create.php');
    exit;
}
?>