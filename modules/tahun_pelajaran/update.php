<?php
// emadrasah/modules/tahun_pelajaran/update.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . base_url('modules/tahun_pelajaran/index.php'));
    exit;
}

if (!validate_csrf_request()) {
    set_flash('danger', 'Token CSRF tidak valid.');
    $redirect = isset($_POST['id']) ? base_url('modules/tahun_pelajaran/edit.php?id=' . (int)$_POST['id']) : base_url('modules/tahun_pelajaran/index.php');
    header('Location: ' . $redirect);
    exit;
}

$id = (int)$_POST['id'];

try {
    $kode = input_safe($_POST['kode']);
    $nama = input_safe($_POST['nama']);
    $is_aktif = isset($_POST['is_aktif']) ? 1 : 0;

    $pdo->beginTransaction();

    // Jika diset aktif, nonaktifkan yang lain dulu
    if ($is_aktif) {
        $pdo->query("UPDATE tahun_pelajaran SET is_aktif = 0");
    }

    $stmt = $pdo->prepare("UPDATE tahun_pelajaran SET kode = ?, nama = ?, is_aktif = ? WHERE id = ?");
    $stmt->execute([$kode, $nama, $is_aktif, $id]);

    $pdo->commit();
    set_flash('success', 'Tahun pelajaran berhasil diperbarui.');
    header('Location: ' . base_url('modules/tahun_pelajaran/index.php'));
    exit;
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    set_flash('danger', 'Terjadi kesalahan sistem: ' . $e->getMessage());
    header('Location: ' . base_url('modules/tahun_pelajaran/edit.php?id=' . $id));
    exit;
}
?>
