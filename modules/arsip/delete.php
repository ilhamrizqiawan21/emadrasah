<?php
// emadrasah/modules/arsip/delete.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Wajib POST + CSRF untuk keamanan
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validate_csrf_request()) {
    set_flash('danger', 'Token CSRF tidak valid atau metode tidak diizinkan.');
    header('Location: ' . base_url('modules/arsip/index.php'));
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id) {
    try {
        $stmt = $pdo->prepare("SELECT file_path FROM arsip_akademik WHERE id = ?");
        $stmt->execute([$id]);
        $file = $stmt->fetchColumn();

        if ($file && file_exists(__DIR__ . '/../../uploads/arsip_akademik/' . $file)) {
            unlink(__DIR__ . '/../../uploads/arsip_akademik/' . $file);
        }

        $pdo->prepare("DELETE FROM arsip_akademik WHERE id = ?")->execute([$id]);
        set_flash('success', 'Arsip berhasil dihapus.');
    } catch (Exception $e) {
        set_flash('danger', 'Error: ' . $e->getMessage());
    }
}
 
header('Location: ' . base_url('modules/arsip/index.php'));
exit;
