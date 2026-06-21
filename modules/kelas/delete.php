<?php
// emadrasah/modules/kelas/delete.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Wajib POST + CSRF untuk keamanan
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validate_csrf_request()) {
    set_flash('danger', 'Token CSRF tidak valid atau metode tidak diizinkan.');
    header('Location: ' . base_url('modules/kelas/index.php'));
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id === 0) {
    set_flash('danger', 'ID kelas tidak valid.');
    header('Location: ' . base_url('modules/kelas/index.php'));
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM kelas WHERE id = ?");
    if ($stmt->execute([$id])) {
        set_flash('success', 'Data kelas berhasil dihapus.');
    } else {
        set_flash('danger', 'Gagal menghapus data kelas.');
    }
} catch (Exception $e) {
    set_flash('danger', 'Terjadi kesalahan sistem: ' . $e->getMessage());
}

header('Location: ' . base_url('modules/kelas/index.php'));
exit;
?>