<?php
// emadrasah/modules/siswa/delete.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Wajib POST + CSRF untuk keamanan
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validate_csrf_request()) {
    set_flash('danger', 'Token CSRF tidak valid atau metode tidak diizinkan.');
    header('Location: index.php');
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

try {
    // Ambil info foto sebelum dihapus untuk pembersihan file
    $stmt = $pdo->prepare("SELECT foto FROM siswa WHERE id = ?");
    $stmt->execute([$id]);
    $foto = $stmt->fetchColumn();

    // Hapus Data (Relasi di database diatur CASCADE sehingga tabel terkait ikut terhapus)
    $del = $pdo->prepare("DELETE FROM siswa WHERE id = ?");
    if ($del->execute([$id])) {
        // Hapus file fisik foto jika ada
        if ($foto && file_exists(__DIR__ . '/../../uploads/foto_siswa/' . $foto)) {
            unlink(__DIR__ . '/../../uploads/foto_siswa/' . $foto);
        }
        set_flash('success', 'Data siswa berhasil dihapus dari sistem.');
    } else {
        set_flash('danger', 'Gagal menghapus data siswa.');
    }
} catch (Exception $e) {
    set_flash('danger', 'Error: ' . $e->getMessage());
}

header('Location: index.php');
exit;
