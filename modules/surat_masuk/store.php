<?php
// emadrasah/modules/surat_masuk/store.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
if (!validate_csrf_request()) {
    set_flash('danger', 'Token CSRF tidak valid.');
    $redirect = 'index.php';
    if (basename($_SERVER['PHP_SELF']) == 'store.php') $redirect = 'create.php';
    if (basename($_SERVER['PHP_SELF']) == 'update.php' && isset($_POST['id'])) $redirect = 'edit.php?id=' . $_POST['id'];
    header('Location: ' . $redirect);
    exit;
}

    try {
        $nomor_agenda = input_safe($_POST['nomor_agenda']);
        $asal_surat = input_safe($_POST['asal_surat']);
        $nomor_surat = input_safe($_POST['nomor_surat']);
        $perihal = input_safe($_POST['perihal']);
        $tanggal_terima = $_POST['tanggal_terima'];
        $tanggal_surat = $_POST['tanggal_surat'] ?: null;
        $status = $_POST['status'];
        $disposisi = input_safe($_POST['disposisi']);
        
        $file_scan = null;
        if (!empty($_FILES['file_scan']['name'])) {
            $ext = pathinfo($_FILES['file_scan']['name'], PATHINFO_EXTENSION);
            $filename = 'SM_' . time() . '.' . $ext;
            $target = __DIR__ . '/../../uploads/surat_masuk/' . $filename;
            if (move_uploaded_file($_FILES['file_scan']['tmp_name'], $target)) {
                $file_scan = $filename;
            }
        }

        $stmt = $pdo->prepare("INSERT INTO surat_masuk (nomor_agenda, asal_surat, nomor_surat, perihal, tanggal_terima, tanggal_surat, status, disposisi, file_scan, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([$nomor_agenda, $asal_surat, $nomor_surat, $perihal, $tanggal_terima, $tanggal_surat, $status, $disposisi, $file_scan]);

        set_flash('success', 'Surat masuk berhasil diregistrasi.');
        header('Location: index.php');
        exit;
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
} else {
    header('Location: index.php');
}
?>