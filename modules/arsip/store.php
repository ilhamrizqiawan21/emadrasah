<?php
// emadrasah/modules/arsip/store.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
if (!validate_csrf_request()) {
    set_flash('danger', 'Token CSRF tidak valid.');
    header('Location: ' . base_url('modules/arsip/create.php'));
    exit;
}

    try {
        $nama = input_safe($_POST['nama_arsip']);
        $tp_id = $_POST['tahun_pelajaran_id'];
        $kelas_id = $_POST['kelas_id'];
        $semester = $_POST['semester'];
        $tipe = input_safe($_POST['tipe']);

        $allowedTipe = ['Leger', 'RDM', 'Lainnya'];
        if (!in_array($tipe, $allowedTipe)) {
            $tipe = 'Lainnya';
        }

        if (!$nama || !$tp_id || !$kelas_id || !$semester) {
            set_flash('danger', 'Lengkapi semua kolom wajib pada form arsip.');
            header('Location: create.php');
            exit;
        }

        if (!isset($_FILES['file_arsip']) || empty($_FILES['file_arsip']['name'])) {
            set_flash('danger', 'File arsip wajib diunggah.');
            header('Location: create.php');
            exit;
        }

        if ($_FILES['file_arsip']['error'] !== UPLOAD_ERR_OK) {
            set_flash('danger', 'Terjadi kesalahan saat mengunggah file.');
            header('Location: create.php');
            exit;
        }

        $ext = strtolower(pathinfo($_FILES['file_arsip']['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'xls', 'xlsx', 'zip'];
        if (!in_array($ext, $allowed)) {
            set_flash('danger', 'Format file tidak didukung. Gunakan PDF, XLS, XLSX, atau ZIP.');
            header('Location: create.php');
            exit;
        }

        if ($_FILES['file_arsip']['size'] > 5 * 1024 * 1024) {
            set_flash('danger', 'Ukuran file maksimal 5MB.');
            header('Location: create.php');
            exit;
        }

        $filename = 'ARSIP_' . time() . '_' . rand(100,999) . '.' . $ext;
        $target = __DIR__ . '/../../uploads/arsip_akademik/' . $filename;
        $file_path = null;

        if (move_uploaded_file($_FILES['file_arsip']['tmp_name'], $target)) {
            $file_path = $filename;
        } else {
            set_flash('danger', 'Gagal mengupload file arsip.');
            header('Location: create.php');
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO arsip_akademik (tahun_pelajaran_id, kelas_id, semester, nama_arsip, file_path, tipe, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([$tp_id, $kelas_id, $semester, $nama, $file_path, $tipe]);

        set_flash('success', 'Arsip akademik berhasil diupload.');
        header('Location: index.php');
        exit;
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}
?>