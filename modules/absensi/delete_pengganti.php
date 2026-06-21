<?php
// emadrasah/modules/absensi/delete_pengganti.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Wajib POST + CSRF untuk keamanan
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validate_csrf_request()) {
    set_flash('danger', 'Token CSRF tidak valid atau metode tidak diizinkan.');
    header('Location: pengganti.php');
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$agenda_id = isset($_POST['agenda_id']) ? (int)$_POST['agenda_id'] : 0;

if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM guru_pengganti WHERE id = ?");
        $stmt->execute([$id]);
        set_flash('success', 'Penugasan guru pengganti telah dibatalkan.');
    } catch (Exception $e) {
        set_flash('danger', 'Error: ' . $e->getMessage());
    }
}

header("Location: pengganti.php?agenda_id=$agenda_id");
exit;
