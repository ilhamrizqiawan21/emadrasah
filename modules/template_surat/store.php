<?php
// emadrasah/modules/template_surat/store.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_request()) {
        set_flash('danger', 'Token CSRF tidak valid.');
        header('Location: create.php');
        exit;
    }
    $nama = input_safe($_POST['nama_template']);
    $konten = $_POST['konten']; // Jangan input_safe karena kita butuh HTML

    try {
        $stmt = $pdo->prepare("INSERT INTO template_surat (nama_template, konten, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
        $stmt->execute([$nama, $konten]);

        set_flash('success', 'Template surat berhasil disimpan.');
        header('Location: index.php');
        exit;
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}
?>