<?php
// emadrasah/modules/jadwal/resolve_kode.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

header('Content-Type: application/json');

$kode = isset($_GET['kode']) ? trim(strtoupper($_GET['kode'])) : '';
if ($kode === '') {
    echo json_encode(['success' => false, 'message' => 'Kode guru diperlukan.']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM gurus WHERE kode = ? LIMIT 1");
$stmt->execute([$kode]);
$guru = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$guru) {
    echo json_encode(['success' => false, 'message' => 'Kode guru tidak ditemukan.']);
    exit;
}

$mapel = null;
if (!empty($guru['bidang_studi'])) {
    $stmt = $pdo->prepare("SELECT * FROM mapels WHERE LOWER(nama_mapel) LIKE ? LIMIT 1");
    $stmt->execute(['%' . strtolower($guru['bidang_studi']) . '%']);
    $mapel = $stmt->fetch(PDO::FETCH_ASSOC);
}

echo json_encode(['success' => true, 'guru' => $guru, 'mapel' => $mapel]);
