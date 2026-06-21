<?php
// emadrasah/modules/tahun_pelajaran/store.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
if (!validate_csrf_request()) {
    set_flash('danger', 'Token CSRF tidak valid.');
    header('Location: ' . base_url('modules/tahun_pelajaran/create.php'));
    exit;
}

    try {
        $kode = input_safe($_POST['kode']);
        $nama = input_safe($_POST['nama']);
        $is_aktif = isset($_POST['is_aktif']) ? 1 : 0;

        $pdo->beginTransaction();

        // Jika diset aktif, nonaktifkan yang lain dulu
        if ($is_aktif) {
            $pdo->query("UPDATE tahun_pelajaran SET is_aktif = 0");
        }

        $stmt = $pdo->prepare("INSERT INTO tahun_pelajaran (kode, nama, is_aktif, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
        $stmt->execute([$kode, $nama, $is_aktif]);

        $pdo->commit();
        set_flash('success', 'Tahun pelajaran berhasil ditambahkan.');
        header('Location: ' . base_url('modules/tahun_pelajaran/index.php'));
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
} else {
    header('Location: ' . base_url('modules/tahun_pelajaran/index.php'));
}
?>