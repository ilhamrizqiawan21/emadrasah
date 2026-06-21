<?php
// emadrasah/modules/buku_induk/delete.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Wajib POST + CSRF untuk keamanan
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validate_csrf_request()) {
    set_flash('danger', 'Token CSRF tidak valid atau metode tidak diizinkan.');
    header('Location: ' . base_url('modules/buku_induk/index.php'));
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

try {
    // Ambil file foto sebelum dihapus
    $stmt = $pdo->prepare("SELECT foto FROM siswa WHERE id = ?");
    $stmt->execute([$id]);
    $foto = $stmt->fetchColumn();

    // Ambil semua file dokumen sebelum dihapus
    $stmt_docs = $pdo->prepare("SELECT file_path FROM siswa_dokumen WHERE siswa_id = ?");
    $stmt_docs->execute([$id]);
    $dokumen_files = $stmt_docs->fetchAll(PDO::FETCH_COLUMN);

    // Hapus data siswa (CASCADE akan hapus relasi di orang_tua_wali, perkembangan_siswa, siswa_dokumen)
    $del = $pdo->prepare("DELETE FROM siswa WHERE id = ?");
    if ($del->execute([$id])) {
        // Hapus file foto
        if ($foto && file_exists(__DIR__ . '/../../uploads/foto_siswa/' . $foto)) {
            unlink(__DIR__ . '/../../uploads/foto_siswa/' . $foto);
        }
        // Hapus file dokumen
        foreach ($dokumen_files as $doc_file) {
            $doc_path = __DIR__ . '/../../uploads/dokumen_siswa/' . $doc_file;
            if (file_exists($doc_path)) {
                unlink($doc_path);
            }
        }
        set_flash('success', 'Data siswa berhasil dihapus dari sistem.');
    } else {
        set_flash('danger', 'Gagal menghapus data siswa.');
    }
} catch (Exception $e) {
    set_flash('danger', 'Terjadi kesalahan sistem. Silakan coba lagi.');
}

header('Location: ' . base_url('modules/buku_induk/index.php'));
exit;
