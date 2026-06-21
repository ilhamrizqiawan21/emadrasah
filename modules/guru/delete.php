<?php
// emadrasah/modules/guru/delete.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Tambahkan validasi CSRF dan pastikan metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validate_csrf_request()) {
    set_flash('danger', 'Token CSRF tidak valid atau metode tidak diizinkan.');
    header('Location: ' . base_url('guru'));
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id === 0) {
    set_flash('danger', 'ID guru tidak valid.');
    header('Location: ' . base_url('guru'));
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM gurus WHERE id = ?");
    if ($stmt->execute([$id])) {
        set_flash('success', 'Data guru berhasil dihapus.');
    } else {
        set_flash('danger', 'Gagal menghapus data guru.');
    }
} catch (Exception $e) {
    set_flash('danger', 'Error: ' . $e->getMessage());
}

header('Location: ' . base_url('guru'));
exit;
?>