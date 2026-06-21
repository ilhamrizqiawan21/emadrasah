<?php
// emadrasah/modules/jam_pelajaran/delete.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Wajib POST + CSRF untuk keamanan
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validate_csrf_request()) {
    set_flash('danger', 'Token CSRF tidak valid atau metode tidak diizinkan.');
    header('Location: ' . base_url('modules/jam_pelajaran/index.php'));
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id === 0) {
    set_flash('danger', 'ID jam pelajaran tidak valid.');
    header('Location: ' . base_url('modules/jam_pelajaran/index.php'));
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM jam_pelajaran WHERE id = ?");
    if ($stmt->execute([$id])) {
        set_flash('success', 'Jam pelajaran berhasil dihapus.');
    } else {
        set_flash('danger', 'Gagal menghapus jam pelajaran.');
    }
} catch (Exception $e) {
    set_flash('danger', 'Error: ' . $e->getMessage());
}

header('Location: ' . base_url('modules/jam_pelajaran/index.php'));
exit;
