<?php
// emadrasah/modules/surat_masuk/update.php
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

    $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $nomor_agenda = input_safe($_POST['nomor_agenda']);
    $asal_surat = input_safe($_POST['asal_surat']);
    $nomor_surat = input_safe($_POST['nomor_surat']);
    $perihal = input_safe($_POST['perihal']);
    $tanggal_terima = $_POST['tanggal_terima'];
    $tanggal_surat = $_POST['tanggal_surat'] ?: null;
    $status = $_POST['status'];
    $disposisi = input_safe($_POST['disposisi']);

    if (!$id || !$nomor_agenda || !$asal_surat || !$perihal || !$tanggal_terima) {
        set_flash('danger', 'Lengkapi semua field wajib.');
        header("Location: edit.php?id=$id");
        exit;
    }

    $stmt = $pdo->prepare("SELECT file_scan FROM surat_masuk WHERE id = ?");
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    $file_scan = $existing['file_scan'] ?? null;

    if (!empty($_FILES['file_scan']['name'])) {
        if ($_FILES['file_scan']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['file_scan']['name'], PATHINFO_EXTENSION));
            $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
            if (!in_array($ext, $allowed)) {
                set_flash('danger', 'Format file tidak didukung. Gunakan PDF, JPG, JPEG, atau PNG.');
                header("Location: edit.php?id=$id");
                exit;
            }
            if ($_FILES['file_scan']['size'] > 10 * 1024 * 1024) {
                set_flash('danger', 'Ukuran file maksimal 10MB.');
                header("Location: edit.php?id=$id");
                exit;
            }
            $uploadDir = __DIR__ . '/../../uploads/surat_masuk/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $filename = 'SM_' . time() . '_' . rand(100,999) . '.' . $ext;
            $target = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['file_scan']['tmp_name'], $target)) {
                if ($file_scan && file_exists($uploadDir . $file_scan)) {
                    unlink($uploadDir . $file_scan);
                }
                $file_scan = $filename;
            }
        }
    }

    $stmt = $pdo->prepare("UPDATE surat_masuk SET nomor_agenda = ?, asal_surat = ?, nomor_surat = ?, perihal = ?, tanggal_terima = ?, tanggal_surat = ?, status = ?, disposisi = ?, file_scan = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$nomor_agenda, $asal_surat, $nomor_surat, $perihal, $tanggal_terima, $tanggal_surat, $status, $disposisi, $file_scan, $id]);

    set_flash('success', 'Data surat masuk berhasil diperbarui.');
    header('Location: index.php');
    exit;
}

header('Location: index.php');
exit;
