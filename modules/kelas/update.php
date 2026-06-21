<?php
// emadrasah/modules/kelas/update.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!validate_csrf_request()) {
    set_flash('danger', 'Token CSRF tidak valid.');
    $redirect = isset($_POST['id']) ? base_url('modules/kelas/edit.php?id=' . (int)$_POST['id']) : base_url('modules/kelas/index.php');
    header('Location: ' . $redirect);
    exit;
}

$id = (int)$_POST['id'];
try {
    $stmt = $pdo->prepare("UPDATE kelas SET nama_kelas = ?, tingkat = ?, guru_pembimbing_id = ?, kapasitas = ?, ruangan = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([
        input_safe($_POST['nama_kelas']),
        input_safe($_POST['tingkat']),
        $_POST['guru_pembimbing_id'] ?: null,
        $_POST['kapasitas'] ?: null,
        input_safe($_POST['ruangan']) ?: null,
        $id
    ]);

    set_flash('success', 'Data kelas berhasil diperbarui.');
    header('Location: index.php');
    exit;
} catch (Exception $e) {
    set_flash('danger', 'Terjadi kesalahan sistem: ' . $e->getMessage());
    header('Location: edit.php?id=' . $id);
    exit;
}
?>