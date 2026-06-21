<?php
// emadrasah/includes/functions.php

/**
 * Inisialisasi Session dengan konfigurasi keamanan Cookie
 */
if (session_status() === PHP_SESSION_NONE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    if (ini_get('session.use_strict_mode') !== '1') {
        ini_set('session.use_strict_mode', '1');
    }

    session_start();
}

/**
 * Base URL helper
 * Menghasilkan URL bersih (tanpa .php dan masking folder modules)
 * Digunakan untuk mendukung sistem routing .htaccess
 */
function base_url($path = '') {
    // Sesuaikan dengan folder project di htdocs/www
    $base_folder = "/emadrasah/";
    
    if (empty($path)) return $base_folder;

    $path = ltrim($path, '/');

    // Jika ini adalah assets, uploads, atau vendor, jangan ubah apapun
    if (preg_match('/^(assets|uploads|vendor)/', $path)) {
        return $base_folder . $path;
    }

    // Pisahkan query string jika ada
    $parts = explode('?', $path, 2);
    $clean_path = $parts[0];
    $query = isset($parts[1]) ? '?' . $parts[1] : '';

    // Hilangkan 'modules/' dari awal path untuk masking siluman sesuai .htaccess
    $clean_path = preg_replace('/^modules\//', '', $clean_path);
    
    // Hilangkan 'index.php' jika itu adalah file index di dalam folder modul
    // Contoh: 'guru/index.php' -> 'guru'
    if ($clean_path === 'index.php') {
        $clean_path = '';
    } else {
        $clean_path = preg_replace('/\/index\.php$/', '', $clean_path);
        // Hilangkan '.php' untuk file lainnya agar sinkron dengan .htaccess
        $clean_path = preg_replace('/\.php$/', '', $clean_path);
    }

    return $base_folder . ltrim($clean_path, '/') . $query;
}

/**
 * Format tanggal Indonesia
 */
function tgl_indo($tanggal) {
    if (!$tanggal || $tanggal == '0000-00-00') return '-';
    $bulan = array (1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
    $pecahkan = explode('-', $tanggal);
    if (count($pecahkan) < 3) return $tanggal;
    return $pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
}

function input_safe($data) {
    // Mencegah XSS dengan membersihkan tag HTML dan karakter khusus
    return htmlspecialchars(strip_tags(trim($data)));
}

function set_flash($type, $message) {
    // Menyimpan pesan sementara ke session (Success/Danger/Info)
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Cross-Site Request Forgery (CSRF) Protection
 */
function get_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_input() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function csrf_meta_tag() {
    return '<meta name="csrf-token" content="' . htmlspecialchars(get_csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function validate_csrf_token($token) {
    return !empty($token) && hash_equals(get_csrf_token(), (string) $token);
}

function validate_csrf_request() {
    $token = '';
    if (!empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
    } elseif (!empty($_POST['csrf_token'])) {
        $token = $_POST['csrf_token'];
    }
    return validate_csrf_token($token);
}

// Mengecek keberadaan kolom di database secara dinamis
function table_has_column(PDO $pdo, string $table, string $column) {
    $stmt = $pdo->prepare("SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ? LIMIT 1");
    $stmt->execute([$table, $column]);
    return (bool) $stmt->fetchColumn();
}

function display_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        $icon = ($flash['type'] == 'success') ? 'fa-check-circle' : (($flash['type'] == 'danger') ? 'fa-times-circle' : 'fa-exclamation-circle');
        echo '<div class="alert alert-'.$flash['type'].' alert-dismissible fade show em-alert em-alert-'.$flash['type'].'" role="alert">
                <i class="fas '.$icon.' me-2"></i>
                '.$flash['message'].'
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
    }
}

/**
 * Validasi required fields
 * @param array $data Data yang divalidasi ($_POST)
 * @param array $fields List field yang wajib ada
 * @return bool|array True jika ok, array error jika gagal
 */
function validate_required($data, $fields) {
    $errors = [];
    foreach ($fields as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            $errors[] = "Field " . str_replace('_', ' ', $field) . " wajib diisi.";
        }
    }
    return empty($errors) ? true : $errors;
}

/**
 * Validasi email
 */
function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Validasi numerik
 */
function validate_numeric($value) {
    return is_numeric($value);
}
?>