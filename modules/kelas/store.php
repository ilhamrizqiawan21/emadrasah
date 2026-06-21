<?php
// emadrasah/modules/kelas/store.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!validate_csrf_request()) {
    set_flash('danger', 'Token CSRF tidak valid.');
    header('Location: ' . base_url('modules/kelas/create.php'));
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO kelas (nama_kelas, tingkat, guru_pembimbing_id, kapasitas, ruangan, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute([
        input_safe($_POST['nama_kelas']),
        input_safe($_POST['tingkat']),
        $_POST['guru_pembimbing_id'] ?: null,
        $_POST['kapasitas'] ?: null,
        input_safe($_POST['ruangan']) ?: null
    ]);

    set_flash('success', 'Data kelas berhasil ditambahkan.');
    header('Location: index.php');
    exit;
} catch (Exception $e) {
    set_flash('danger', 'Terjadi kesalahan sistem: ' . $e->getMessage());
    header('Location: create.php');
    exit;
}
?>