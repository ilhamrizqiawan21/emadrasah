<?php
// emadrasah/modules/surat_keluar/delete.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Wajib POST + CSRF untuk keamanan
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validate_csrf_request()) {
    set_flash('danger', 'Token CSRF tidak valid atau metode tidak diizinkan.');
    header('Location: index.php');
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

try {
    // Ambil info file scan untuk dihapus
    $stmt = $pdo->prepare("SELECT file_draft FROM surat_keluar WHERE id = ?");
    $stmt->execute([$id]);
    $surat = $stmt->fetch();

    if ($surat && $surat['file_draft']) {
        $filePath = __DIR__ . '/../../uploads/surat_keluar/' . $surat['file_draft'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    $stmt = $pdo->prepare("DELETE FROM surat_keluar WHERE id = ?");
    $stmt->execute([$id]);

    set_flash('success', 'Data surat keluar berhasil dihapus.');
    header('Location: index.php');
    exit;
} catch (Exception $e) {
    set_flash('danger', 'Error: ' . $e->getMessage());
    header('Location: index.php');
    exit;
}
