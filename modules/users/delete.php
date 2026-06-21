<?php
// emadrasah/modules/users/delete.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Wajib POST + CSRF untuk keamanan
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validate_csrf_request()) {
    set_flash('danger', 'Token CSRF tidak valid atau metode tidak diizinkan.');
    header('Location: index.php');
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$currentUserId = $_SESSION['user_id'] ?? 0;

if ($currentUserId && $currentUserId === $id) {
    set_flash('error', 'Anda tidak dapat menghapus akun yang sedang digunakan.');
    header('Location: index.php');
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);

    set_flash('success', 'User berhasil dihapus.');
    header('Location: index.php');
    exit;
} catch (Exception $e) {
    set_flash('danger', 'Error: ' . $e->getMessage());
    header('Location: index.php');
    exit;
}
