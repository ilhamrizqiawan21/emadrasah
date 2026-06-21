<?php
// emadrasah/modules/surat_keluar/update.php
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

    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $nomor_surat = input_safe($_POST['nomor_surat']);
    $tujuan = input_safe($_POST['tujuan']);
    $perihal = input_safe($_POST['perihal']);
    $tanggal_kirim = $_POST['tanggal_kirim'];
    $lampiran = input_safe($_POST['lampiran']);
    $template_id = null;
    $hasTemplateColumn = table_has_column($pdo, 'surat_keluar', 'template_id');
    if ($hasTemplateColumn) {
        $template_id = !empty($_POST['template_id']) ? (int)$_POST['template_id'] : null;
    }

    if (!$id || !$nomor_surat || !$tujuan || !$perihal || !$tanggal_kirim) {
        set_flash('danger', 'Lengkapi semua field wajib.');
        header("Location: edit.php?id=$id");
        exit;
    }

    $stmt = $pdo->prepare("SELECT file_draft FROM surat_keluar WHERE id = ?");
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    $file_draft = $existing['file_draft'] ?? null;

    if (!empty($_FILES['file_draft']['name'])) {
        if ($_FILES['file_draft']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['file_draft']['name'], PATHINFO_EXTENSION));
            $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
            if (!in_array($ext, $allowed)) {
                set_flash('danger', 'Format file tidak didukung. Gunakan PDF, JPG, JPEG, atau PNG.');
                header("Location: edit.php?id=$id");
                exit;
            }
            if ($_FILES['file_draft']['size'] > 10 * 1024 * 1024) {
                set_flash('danger', 'Ukuran file maksimal 10MB.');
                header("Location: edit.php?id=$id");
                exit;
            }
            $uploadDir = __DIR__ . '/../../uploads/surat_keluar/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $filename = 'SK_' . time() . '_' . rand(100,999) . '.' . $ext;
            $target = $uploadDir . $filename;
            if (move_uploaded_file($_FILES['file_draft']['tmp_name'], $target)) {
                if ($file_draft && file_exists($uploadDir . $file_draft)) {
                    unlink($uploadDir . $file_draft);
                }
                $file_draft = $filename;
            }
        }
    }

    if ($hasTemplateColumn) {
        $stmt = $pdo->prepare("UPDATE surat_keluar SET nomor_surat = ?, tujuan = ?, perihal = ?, tanggal_kirim = ?, lampiran = ?, file_draft = ?, template_id = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$nomor_surat, $tujuan, $perihal, $tanggal_kirim, $lampiran, $file_draft, $template_id, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE surat_keluar SET nomor_surat = ?, tujuan = ?, perihal = ?, tanggal_kirim = ?, lampiran = ?, file_draft = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$nomor_surat, $tujuan, $perihal, $tanggal_kirim, $lampiran, $file_draft, $id]);
    }

    set_flash('success', 'Data surat keluar berhasil diperbarui.');
    header('Location: index.php');
    exit;
}

header('Location: index.php');
exit;
