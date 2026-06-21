<?php
// emadrasah/modules/tahun_pelajaran/delete.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Wajib POST + CSRF untuk keamanan
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validate_csrf_request()) {
    set_flash('danger', 'Token CSRF tidak valid atau metode tidak diizinkan.');
    header('Location: ' . base_url('modules/tahun_pelajaran/index.php'));
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id === 0) {
    set_flash('danger', 'ID tahun pelajaran tidak valid.');
    header('Location: ' . base_url('modules/tahun_pelajaran/index.php'));
    exit;
}

try {
    // Cek apakah tahun pelajaran yang akan dihapus sedang aktif
    $stmt = $pdo->prepare("SELECT is_aktif FROM tahun_pelajaran WHERE id = ?");
    $stmt->execute([$id]);
    $tp = $stmt->fetch();

    if (!$tp) {
        set_flash('danger', 'Data tahun pelajaran tidak ditemukan.');
        header('Location: ' . base_url('modules/tahun_pelajaran/index.php'));
        exit;
    }

    if ($tp['is_aktif']) {
        set_flash('danger', 'Gagal: Tahun pelajaran yang sedang aktif tidak boleh dihapus.');
        header('Location: ' . base_url('modules/tahun_pelajaran/index.php'));
        exit;
    }

    // Lakukan penghapusan (akan men-cascade relasi asing yang menggunakan ON DELETE CASCADE jika ada)
    $stmt_del = $pdo->prepare("DELETE FROM tahun_pelajaran WHERE id = ?");
    if ($stmt_del->execute([$id])) {
        set_flash('success', 'Tahun pelajaran berhasil dihapus.');
    } else {
        set_flash('danger', 'Gagal menghapus tahun pelajaran.');
    }
} catch (Exception $e) {
    set_flash('danger', 'Terjadi kesalahan sistem: ' . $e->getMessage());
}

header('Location: ' . base_url('modules/tahun_pelajaran/index.php'));
exit;
