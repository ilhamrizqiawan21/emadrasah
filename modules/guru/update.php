<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_request()) {
        set_flash('danger', 'Token CSRF tidak valid.');
        header('Location: ' . base_url('guru/edit.php?id=' . urlencode($_POST['id'])));
        exit;
    }

    $id = (int)$_POST['id'];
    $required = ['kode', 'nama'];
    $errors = [];
    foreach ($required as $field) {
        if (empty(trim($_POST[$field]))) {
            $errors[] = "Field " . str_replace('_', ' ', $field) . " wajib diisi.";
        }
    }
    if (!empty($errors)) {
        set_flash('danger', implode('<br>', $errors));
        header('Location: ' . base_url('guru/edit.php?id=' . $id));
        exit;
    }

    try {
        $stmt = $pdo->prepare("UPDATE gurus SET 
            kode = ?, 
            nama = ?, 
            nip = ?, 
            bidang_studi = ?, 
            email = ?, 
            phone = ?, 
            status = ?,
            updated_at = NOW() 
            WHERE id = ?");
        
        $stmt->execute([
            input_safe($_POST['kode']),
            input_safe($_POST['nama']),
            !empty($_POST['nip']) ? input_safe($_POST['nip']) : null,
            !empty($_POST['bidang_studi']) ? input_safe($_POST['bidang_studi']) : null,
            !empty($_POST['email']) ? input_safe($_POST['email']) : null,
            !empty($_POST['phone']) ? input_safe($_POST['phone']) : null,
            !empty($_POST['status']) ? input_safe($_POST['status']) : 'aktif',
            $id
        ]);
        $stmt = $pdo->prepare("UPDATE gurus SET kode=?, nama=?, nip=?, bidang_studi=?, email=?, phone=?, status=?, beban_jp=?, updated_at=NOW() WHERE id=?");

        set_flash('success', 'Data guru berhasil diperbarui.');
        header('Location: ' . base_url('guru'));
        exit;
    } catch (PDOException $e) {
        if ($e->errorInfo[1] == 1062) {
            set_flash('danger', 'Kode, Email, atau NIP sudah terdaftar. Silakan gunakan yang unik.');
        } else {
            set_flash('danger', 'Gagal memperbarui: ' . $e->getMessage());
        }
        header('Location: ' . base_url('guru/edit.php?id=' . $id));
        exit;
    }
} else {
    header('Location: ' . base_url('guru'));
    exit;
}