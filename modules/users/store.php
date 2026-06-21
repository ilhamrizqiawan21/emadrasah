<?php
// emadrasah/modules/users/store.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!validate_csrf_request()) {
    set_flash('danger', 'Token CSRF tidak valid.');
    header('Location: create.php');
    exit;
}

// Validasi Required
$val = validate_required($_POST, ['name', 'email', 'password', 'password_confirmation']);
if ($val !== true) {
    set_flash('danger', implode('<br>', $val));
    header('Location: create.php');
    exit;
}

// Validasi Email
if (!validate_email($_POST['email'])) {
    set_flash('danger', 'Format email tidak valid.');
    header('Location: create.php');
    exit;
}

$name = input_safe($_POST['name'] ?? '');
$email = input_safe($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$password_confirmation = $_POST['password_confirmation'] ?? '';
$role = input_safe($_POST['role'] ?? 'operator');
$is_active = isset($_POST['is_active']) ? (int) $_POST['is_active'] : 1;
$phone = input_safe($_POST['phone'] ?? '');
$alamat = input_safe($_POST['alamat'] ?? '');

if ($password !== $password_confirmation) {
    set_flash('danger', 'Password dan konfirmasi password tidak cocok.');
    header('Location: create.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        set_flash('error', 'Email sudah terdaftar.');
        header('Location: create.php');
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, is_active, phone, alamat, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute([
        $name,
        $email,
        password_hash($password, PASSWORD_DEFAULT),
        $role,
        $is_active,
        $phone ?: null,
        $alamat ?: null,
    ]);

    set_flash('success', 'User berhasil ditambahkan.');
    header('Location: index.php');
    exit;
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
