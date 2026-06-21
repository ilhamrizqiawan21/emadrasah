<?php
// emadrasah/modules/tahun_pelajaran/activate.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validate_csrf_request()) {
    set_flash('danger', 'Token CSRF tidak valid atau metode tidak diizinkan.');
    header('Location: ' . base_url('modules/tahun_pelajaran/index.php'));
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id) {
    try {
        $pdo->beginTransaction();

        // Nonaktifkan semua
        $pdo->query("UPDATE tahun_pelajaran SET is_aktif = 0");

        // Aktifkan yang dipilih
        $stmt = $pdo->prepare("UPDATE tahun_pelajaran SET is_aktif = 1 WHERE id = ?");
        $stmt->execute([$id]);

        $pdo->commit();
        set_flash('success', 'Tahun pelajaran aktif berhasil diubah.');
    } catch (Exception $e) {
        $pdo->rollBack();
        set_flash('danger', 'Error: ' . $e->getMessage());
    }
}

header('Location: ' . base_url('modules/tahun_pelajaran/index.php'));
exit;
