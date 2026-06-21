<?php
// emadrasah/modules/tasks/update.php
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

    $id = $_POST['id'];
    try {
        $stmt = $pdo->prepare("SELECT attachment FROM tasks WHERE id = ?");
        $stmt->execute([$id]);
        $task = $stmt->fetch();

        $attachment = $task['attachment'] ?? null;
        $uploadedAttachment = $attachment;

        if (!empty($_FILES['attachment']['name'])) {
            if ($_FILES['attachment']['error'] !== UPLOAD_ERR_OK) {
                set_flash('danger', 'Terjadi kesalahan saat mengunggah lampiran.');
                header("Location: edit.php?id=$id");
                exit;
            }

            $ext = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));
            $allowed = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip'];
            if (!in_array($ext, $allowed)) {
                set_flash('danger', 'Format lampiran tidak didukung. Gunakan PDF, DOC, DOCX, XLS, XLSX, atau ZIP.');
                header("Location: edit.php?id=$id");
                exit;
            }

            if ($_FILES['attachment']['size'] > 10 * 1024 * 1024) {
                set_flash('danger', 'Ukuran lampiran maksimal 10MB.');
                header("Location: edit.php?id=$id");
                exit;
            }

            $uploadDir = __DIR__ . '/../../uploads/tasks/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $filename = 'TASK_' . time() . '_' . rand(100,999) . '.' . $ext;
            $target = $uploadDir . $filename;

            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target)) {
                $uploadedAttachment = $filename;
                if ($attachment && file_exists($uploadDir . $attachment)) {
                    unlink($uploadDir . $attachment);
                }
            } else {
                set_flash('danger', 'Gagal menyimpan lampiran baru.');
                header("Location: edit.php?id=$id");
                exit;
            }
        }

        $pdo->beginTransaction();

        $stmt = $pdo->prepare("UPDATE tasks SET judul = ?, deskripsi = ?, assigned_to = ?, kategori = ?, prioritas = ?, deadline = ?, attachment = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([
            input_safe($_POST['judul']),
            input_safe($_POST['deskripsi']),
            $_POST['assigned_to'] ?: null,
            input_safe($_POST['kategori']),
            $_POST['prioritas'],
            $_POST['deadline'],
            $uploadedAttachment,
            $id
        ]);

        // Catat Log Perubahan
        $stmt_log = $pdo->prepare("INSERT INTO task_logs (task_id, user_id, action, keterangan, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
        $stmt_log->execute([
            $id,
            $_SESSION['user_id'] ?? null,
            'Perubahan Data',
            "Detail tugas (Judul/Deadline/Prioritas/Lampiran) telah diperbarui."
        ]);

        $pdo->commit();
        set_flash('success', 'Data tugas berhasil diperbarui.');
        header("Location: index.php");
        exit;
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}
?>