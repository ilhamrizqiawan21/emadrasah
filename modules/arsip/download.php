<?php
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../config/database.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
    set_flash('danger', 'Arsip tidak ditemukan.');
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT nama_arsip, file_path FROM arsip_akademik WHERE id = ?');
$stmt->execute([$id]);
$arsip = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$arsip || !$arsip['file_path']) {
    set_flash('danger', 'File arsip tidak ditemukan.');
    header('Location: index.php');
    exit;
}

$file = __DIR__ . '/../../uploads/arsip_akademik/' . $arsip['file_path'];
if (!file_exists($file)) {
    set_flash('danger', 'File arsip fisik tidak ditemukan.');
    header('Location: index.php');
    exit;
}

$filename = basename($arsip['file_path']);
$mime = mime_content_type($file) ?: 'application/octet-stream';

header('Content-Description: File Transfer');
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file));
readfile($file);
exit;
