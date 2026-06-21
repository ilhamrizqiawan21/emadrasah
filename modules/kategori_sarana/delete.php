
























<?php
// emadrasah/modules/kategori_sarana/delete.php
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
    set_flash('danger', 'ID kategori tidak valid.');
    header('Location: index.php');
    exit;
}

try {
    // Cek apakah ada sarana yang menggunakan kategori ini
    $stmt_cek = $pdo->prepare("SELECT COUNT(*) FROM sarana_prasarana WHERE kategori_id = ?");
    $stmt_cek->execute([$id]);
    if ($stmt_cek->fetchColumn() > 0) {
        set_flash('danger', 'Gagal: Kategori tidak bisa dihapus karena masih digunakan oleh data aset.');
    } else {
        $pdo->prepare("DELETE FROM kategori_sarana WHERE id = ?")->execute([$id]);
        set_flash('success', 'Kategori berhasil dihapus.');
    }
} catch (Exception $e) {
    set_flash('danger', 'Terjadi kesalahan sistem: ' . $e->getMessage());
}

header('Location: index.php');
exit;
?>