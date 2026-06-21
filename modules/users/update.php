<?php
// emadrasah/modules/users/update.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!validate_csrf_request()) {
    set_flash('danger', 'Token CSRF tidak valid.');
    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    header('Location: edit.php' . ($id ? '?id=' . $id : ''));
    exit;
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
$name = input_safe($_POST['name'] ?? '');
$email = input_safe($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$password_confirmation = $_POST['password_confirmation'] ?? '';
$role = input_safe($_POST['role'] ?? 'operator');
$is_active = isset($_POST['is_active']) ? (int) $_POST['is_active'] : 1;
$phone = input_safe($_POST['phone'] ?? '');
$alamat = input_safe($_POST['alamat'] ?? '');

if (!$id || !$name || !$email) {
    set_flash('error', 'ID, nama, dan email wajib diisi.');
    header('Location: edit.php?id=' . $id);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $id]);
    if ($stmt->fetchColumn() > 0) {
        set_flash('error', 'Email sudah terdaftar oleh user lain.');
        header('Location: edit.php?id=' . $id);
        exit;
    }

    if ($password) {
        if ($password !== $password_confirmation) {
            set_flash('error', 'Password dan konfirmasi password tidak cocok.');
            header('Location: edit.php?id=' . $id);
            exit;
        }
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ?, role = ?, is_active = ?, phone = ?, alamat = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([
            $name,
            $email,
            password_hash($password, PASSWORD_DEFAULT),
            $role,
            $is_active,
            $phone ?: null,
            $alamat ?: null,
            $id,
        ]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, role = ?, is_active = ?, phone = ?, alamat = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([
            $name,
            $email,
            $role,
            $is_active,
            $phone ?: null,
            $alamat ?: null,
            $id,
        ]);
    }

    set_flash('success', 'User berhasil diperbarui.');
    header('Location: index.php');
    exit;
} catch (Exception $e) {
    die('Error: ' . $e->getMessage());
}
