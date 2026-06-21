<?php
// emadrasah/modules/tasks/store.php
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
        $judul = input_safe($_POST['judul']);
        $deskripsi = input_safe($_POST['deskripsi']);
        $assigned_to = $_POST['assigned_to'] ?: null;
        $kategori = input_safe($_POST['kategori']);
        $prioritas = $_POST['prioritas'] ?: 'sedang';
        $deadline = $_POST['deadline'];

        if (!$judul || !$deadline) {
            set_flash('danger', 'Lengkapi judul dan deadline tugas.');
            header('Location: create.php');
            exit;
        }

        $attachment = null;
        if (!empty($_FILES['attachment']['name'])) {
            if ($_FILES['attachment']['error'] !== UPLOAD_ERR_OK) {
                set_flash('danger', 'Terjadi kesalahan saat mengunggah lampiran.');
                header('Location: create.php');
                exit;
            }

            $ext = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
            $allowed = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip'];
            if (!in_array($ext, $allowed)) {
                set_flash('danger', 'Format lampiran tidak didukung. Gunakan PDF, DOC, DOCX, XLS, XLSX, atau ZIP.');
                header('Location: create.php');
                exit;
            }

            if ($_FILES['attachment']['size'] > 10 * 1024 * 1024) {
                set_flash('danger', 'Ukuran lampiran maksimal 10MB.');
                header('Location: create.php');
                exit;
            }

            $uploadDir = __DIR__ . '/../../uploads/tasks/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $filename = 'TASK_' . time() . '_' . rand(100,999) . '.' . $ext;
            $target = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target)) {
                $attachment = $filename;
            } else {
                set_flash('danger', 'Gagal menyimpan lampiran tugas.');
                header('Location: create.php');
                exit;
            }
        }

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO tasks (judul, deskripsi, assigned_to, kategori, prioritas, deadline, status, progress_persen, attachment, created_by, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, 'antrean', 0, ?, ?, NOW(), NOW())");
        $stmt->execute([
            $judul,
            $deskripsi,
            $assigned_to,
            $kategori,
            $prioritas,
            $deadline,
            $attachment,
            $_SESSION['user_id'] ?? null
        ]);

        $task_id = $pdo->lastInsertId();

        // Catat Log Awal
        $stmt_log = $pdo->prepare("INSERT INTO task_logs (task_id, user_id, action, keterangan, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
        $stmt_log->execute([
            $task_id,
            $_SESSION['user_id'] ?? null,
            'Tugas Dibuat',
            "Tugas baru ditambahkan ke sistem."
        ]);

        $pdo->commit();
        set_flash('success', 'Tugas baru berhasil dibuat.');
        header('Location: index.php');
        exit;
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}
?>