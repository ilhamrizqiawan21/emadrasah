<?php
// emadrasah/modules/tasks/delete.php
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
        $stmt = $pdo->prepare("SELECT attachment FROM tasks WHERE id = ?");
        $stmt->execute([$id]);
        $task = $stmt->fetch();

        if ($task && $task['attachment']) {
            $filePath = __DIR__ . '/../../uploads/tasks/' . $task['attachment'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
        $stmt->execute([$id]);
        set_flash('success', 'Tugas berhasil dihapus.');
    } catch (Exception $e) {
        set_flash('danger', 'Error: ' . $e->getMessage());
    }
}

header('Location: index.php');
exit;
