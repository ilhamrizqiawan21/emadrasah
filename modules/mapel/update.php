<?php
// emadrasah/modules/mapel/update.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!validate_csrf_request()) {
    set_flash('danger', 'Token CSRF tidak valid.');
    header('Location: ' . base_url('modules/mapel/edit.php?id=' . (int)$_POST['id']));
    exit;
}

$id = (int)$_POST['id'];
try {
    $stmt = $pdo->prepare("UPDATE mapels SET nama_mapel = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([
        input_safe($_POST['nama_mapel']),
        $id
    ]);

    set_flash('success', 'Mata pelajaran berhasil diperbarui.');
    header('Location: index.php');
    exit;
} catch (Exception $e) {
    set_flash('danger', 'Terjadi kesalahan sistem: ' . $e->getMessage());
    header('Location: edit.php?id=' . $id);
    exit;
}
?>