<?php
/**
 * Auth Helper - emadrasah/includes/auth.php
 */

/**
 * Memastikan user sudah login, jika belum lempar ke halaman login
 */
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . base_url('login.php'));
        exit;
    }
}

/**
 * Mengambil data user yang sedang login dari session
 */
function current_user() {
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'name' => $_SESSION['user_name'] ?? null,
        'role' => $_SESSION['user_role'] ?? null,
    ];
}

/**
 * Mengecek apakah user memiliki role tertentu
 * @param string|array $role Nama role (string) atau list role (array)
 * @return bool
 */
function has_role($role) {
    $user = current_user();
    if (!$user['role']) return false;
    
    $user_role = strtolower($user['role']);
    
    if (is_array($role)) {
        return in_array($user_role, array_map('strtolower', $role));
    }
    
    return $user_role === strtolower($role);
}

/**
 * Membatasi akses berdasarkan role, jika tidak sesuai lempar ke dashboard
 */
function require_role($role) {
    if (!has_role($role)) {
        set_flash('danger', 'Anda tidak memiliki hak akses untuk halaman tersebut.');
        header('Location: ' . base_url('index.php'));
        exit;
    }
}
?>
