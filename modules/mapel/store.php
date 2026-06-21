<?php
// emadrasah/modules/mapel/store.php
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
    $stmt = $pdo->prepare("INSERT INTO mapels (nama_mapel, created_at, updated_at) VALUES (?, NOW(), NOW())");
    $stmt->execute([
        input_safe($_POST['nama_mapel'])
    ]);

    set_flash('success', 'Mata pelajaran berhasil ditambahkan.');
    header('Location: index.php');
    exit;
} catch (Exception $e) {
    set_flash('danger', 'Terjadi kesalahan sistem: ' . $e->getMessage());
    header('Location: create.php');
    exit;
}
?>