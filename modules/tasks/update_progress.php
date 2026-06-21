<?php
// emadrasah/modules/tasks/update_progress.php
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
    $status = $_POST['status'];
    $progress = $_POST['progress_persen'];

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("UPDATE tasks SET status = ?, progress_persen = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$status, $progress, $id]);

        // Catat Log Perubahan
        $user_id = $_SESSION['user_id'] ?? null;
        $stmt_log = $pdo->prepare("INSERT INTO task_logs (task_id, user_id, action, keterangan, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
        $stmt_log->execute([
            $id, 
            $user_id, 
            'Update Progres', 
            "Status diubah ke '" . ucfirst($status) . "' dengan progres " . $progress . "%"
        ]);

        $pdo->commit();
        set_flash('success', 'Progress tugas berhasil diupdate.');
        header("Location: edit.php?id=$id");
        exit;
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}
?>