<?php
// emadrasah/modules/mapel/delete.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Wajib POST + CSRF untuk keamanan
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validate_csrf_request()) {
    set_flash('danger', 'Token CSRF tidak valid atau metode tidak diizinkan.');
    header('Location: index.php');
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id === 0) {
    set_flash('danger', 'ID mata pelajaran tidak valid.');
    header('Location: index.php');
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM mapels WHERE id = ?");
    if ($stmt->execute([$id])) {
        set_flash('success', 'Mata pelajaran berhasil dihapus.');
    } else {
        set_flash('danger', 'Gagal menghapus mata pelajaran.');
    }
} catch (Exception $e) {
    set_flash('danger', 'Terjadi kesalahan sistem: ' . $e->getMessage());
}

header('Location: index.php');
exit;
?>