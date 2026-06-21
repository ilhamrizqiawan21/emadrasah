<?php
// emadrasah/modules/sarana/delete.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Wajib POST + CSRF untuk keamanan
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validate_csrf_request()) {
    set_flash('danger', 'Token CSRF tidak valid atau metode tidak diizinkan.');
    header('Location: index.php');
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id) {
    try {
        // Hapus foto fisik jika ada
        $stmt = $pdo->prepare("SELECT foto FROM sarana_prasarana WHERE id = ?");
        $stmt->execute([$id]);
        $foto = $stmt->fetchColumn();
        
        if ($foto && file_exists(__DIR__ . '/../../uploads/sarana/' . $foto)) {
            unlink(__DIR__ . '/../../uploads/sarana/' . $foto);
        }

        $stmt = $pdo->prepare("DELETE FROM sarana_prasarana WHERE id = ?");
        $stmt->execute([$id]);
        set_flash('success', 'Data aset berhasil dihapus.');
    } catch (Exception $e) {
        set_flash('danger', 'Error: ' . $e->getMessage());
    }
}

header('Location: index.php');
exit;
