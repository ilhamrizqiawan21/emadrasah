<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_request()) {
        set_flash('danger', 'Token CSRF tidak valid.');
        header('Location: ' . base_url('guru/create'));
        exit;
    }

    // Validasi required
    $required = ['kode', 'nama'];
    $errors = [];
    foreach ($required as $field) {
        if (empty(trim($_POST[$field]))) {
            $errors[] = "Field " . str_replace('_', ' ', $field) . " wajib diisi.";
        }
    }
    if (!empty($errors)) {
        set_flash('danger', implode('<br>', $errors));
        header('Location: ' . base_url('guru/create'));
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO gurus 
            (kode, nama, nip, bidang_studi, email, phone, status, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        
        $stmt->execute([
            input_safe($_POST['kode']),
            input_safe($_POST['nama']),
            !empty($_POST['nip']) ? input_safe($_POST['nip']) : null,
            !empty($_POST['bidang_studi']) ? input_safe($_POST['bidang_studi']) : null,
            !empty($_POST['email']) ? input_safe($_POST['email']) : null,
            !empty($_POST['phone']) ? input_safe($_POST['phone']) : null,
            !empty($_POST['status']) ? input_safe($_POST['status']) : 'aktif'
        ]);

        set_flash('success', 'Data guru berhasil ditambahkan.');
        header('Location: ' . base_url('guru'));
        exit;
    } catch (PDOException $e) {
        // Cek jika duplikat kode, email, atau nip
        if ($e->errorInfo[1] == 1062) {
            set_flash('danger', 'Kode, Email, atau NIP sudah terdaftar. Silakan gunakan yang unik.');
        } else {
            set_flash('danger', 'Gagal menyimpan: ' . $e->getMessage());
        }
        header('Location: ' . base_url('guru/create'));
        exit;
    }
} else {
    header('Location: ' . base_url('guru'));
    exit;
}