<?php
// emadrasah/modules/template_surat/update.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_request()) {
        set_flash('danger', 'Token CSRF tidak valid.');
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        header("Location: edit.php" . ($id ? "?id=$id" : ""));
        exit;
    }
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $nama = input_safe($_POST['nama_template']);
    $konten = $_POST['konten'];

    if (!$id || !$nama || !$konten) {
        set_flash('danger', 'Nama template dan konten wajib diisi.');
        header("Location: edit.php?id=$id");
        exit;
    }

    $stmt = $pdo->prepare("UPDATE template_surat SET nama_template = ?, konten = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$nama, $konten, $id]);

    set_flash('success', 'Template surat berhasil diperbarui.');
    header('Location: index.php');
    exit;
}

header('Location: index.php');
exit;
