<?php
// emadrasah/modules/surat_masuk/delete.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Wajib POST + CSRF untuk keamanan
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validate_csrf_request()) {
    set_flash('danger', 'Token CSRF tidak valid atau metode tidak diizinkan.');
    header('Location: index.php');
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
if ($id) {
    $stmt = $pdo->prepare("SELECT file_scan FROM surat_masuk WHERE id = ?");
    $stmt->execute([$id]);
    $surat = $stmt->fetch();

    if ($surat && $surat['file_scan']) {
        $filePath = __DIR__ . '/../../uploads/surat_masuk/' . $surat['file_scan'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    $stmt = $pdo->prepare("DELETE FROM surat_masuk WHERE id = ?");
    $stmt->execute([$id]);
    set_flash('success', 'Surat masuk berhasil dihapus.');
}

header('Location: index.php');
exit;
