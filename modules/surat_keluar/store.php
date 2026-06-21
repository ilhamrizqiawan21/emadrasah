<?php
// emadrasah/modules/surat_keluar/store.php
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
        
        $file_draft = null;
        if (!empty($_FILES['file_draft']['name'])) {
            $ext = pathinfo($_FILES['file_draft']['name'], PATHINFO_EXTENSION);
            $filename = 'SK_' . time() . '.' . $ext;
            $target = __DIR__ . '/../../uploads/surat_keluar/' . $filename;
            if (move_uploaded_file($_FILES['file_draft']['tmp_name'], $target)) {
                $file_draft = $filename;
            }
        }

        if ($hasTemplateColumn) {
            $stmt = $pdo->prepare("INSERT INTO surat_keluar (nomor_surat, tujuan, perihal, tanggal_kirim, lampiran, file_draft, template_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([$nomor_surat, $tujuan, $perihal, $tanggal_kirim, $lampiran, $file_draft, $template_id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO surat_keluar (nomor_surat, tujuan, perihal, tanggal_kirim, lampiran, file_draft, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([$nomor_surat, $tujuan, $perihal, $tanggal_kirim, $lampiran, $file_draft]);
        }

        set_flash('success', 'Surat keluar berhasil diregistrasi.');
        header('Location: index.php');
        exit;
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
} else {
    header('Location: index.php');
}
?>