<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

require_login();

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

$results = [];

// 1. Cari Siswa
$stmt = $pdo->prepare("SELECT id, nama_lengkap as title, 'Siswa' as category, CONCAT('modules/siswa/show.php?id=', id) as url FROM siswa WHERE nama_lengkap LIKE :q OR nis LIKE :q LIMIT 5");
$stmt->execute(['q' => "%$q%"]);
$results = array_merge($results, $stmt->fetchAll());

// 2. Cari Guru
$stmt = $pdo->prepare("SELECT id, nama as title, 'Guru' as category, CONCAT('modules/guru/show.php?id=', id) as url FROM gurus WHERE nama LIKE :q OR nip LIKE :q LIMIT 5");
$stmt->execute(['q' => "%$q%"]);
$results = array_merge($results, $stmt->fetchAll());

// 3. Cari Surat Masuk
$stmt = $pdo->prepare("SELECT id, perihal as title, 'Surat Masuk' as category, CONCAT('modules/surat_masuk/show.php?id=', id) as url FROM surat_masuk WHERE perihal LIKE :q OR nomor_surat LIKE :q LIMIT 5");
$stmt->execute(['q' => "%$q%"]);
$results = array_merge($results, $stmt->fetchAll());

header('Content-Type: application/json');
echo json_encode($results);
