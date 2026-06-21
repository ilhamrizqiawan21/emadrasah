<?php
// emadrasah/modules/jadwal/store.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!validate_csrf_request()) {
    set_flash('danger', 'Token CSRF tidak valid.');
    header('Location: create.php');
    exit;
}

try {
    // Ambil & bersihkan input
    $kelas_id   = isset($_POST['kelas_id']) ? (int)$_POST['kelas_id'] : 0;
    $jam_id     = isset($_POST['jam_id']) ? (int)$_POST['jam_id'] : 0;
    $guru_id    = isset($_POST['guru_id']) ? (int)$_POST['guru_id'] : 0;
    $mapel_id   = isset($_POST['mapel_id']) ? (int)$_POST['mapel_id'] : 0;
    $tahun_ajaran = isset($_POST['tahun_ajaran']) ? trim($_POST['tahun_ajaran']) : '';
    $semester   = isset($_POST['semester']) ? (int)$_POST['semester'] : 1;
    $ruang      = isset($_POST['ruang']) ? trim($_POST['ruang']) : null;

    // Validasi required
    $errors = [];
    if ($kelas_id <= 0) $errors[] = 'Kelas wajib dipilih.';
    if ($jam_id <= 0) $errors[] = 'Jam pelajaran wajib dipilih.';
    if ($guru_id <= 0) $errors[] = 'Guru wajib dipilih.';
    if ($mapel_id <= 0) $errors[] = 'Mata pelajaran wajib dipilih.';
    if (empty($tahun_ajaran)) $errors[] = 'Tahun pelajaran wajib dipilih.';
    if ($semester < 1 || $semester > 2) $errors[] = 'Semester tidak valid.';

    if (!empty($errors)) {
        set_flash('danger', implode('<br>', $errors));
        header('Location: create.php');
        exit;
    }

    // Ambil data jam_pelajaran berdasarkan jam_id
    $stmt = $pdo->prepare("SELECT * FROM jam_pelajaran WHERE id = ?");
    $stmt->execute([$jam_id]);
    $jam = $stmt->fetch();

    if (!$jam) {
        set_flash('danger', 'Jam pelajaran tidak ditemukan.');
        header('Location: create.php');
        exit;
    }

    $hari = $jam['hari'];
    $jam_mulai = $jam['jam_mulai'];
    $jam_selesai = $jam['jam_selesai'];

    // Cek duplikat: kelas + hari + jam_mulai + jam_selesai + semester + tahun_ajaran
    $stmtCek = $pdo->prepare("SELECT COUNT(*) FROM jadwals WHERE kelas_id = ? AND hari = ? AND jam_mulai = ? AND jam_selesai = ? AND semester = ? AND tahun_ajaran = ? AND guru_id = ?");
    $stmtCek->execute([$kelas_id, $hari, $jam_mulai, $jam_selesai, $semester, $tahun_ajaran, $guru_id]);
    if ($stmtCek->fetchColumn() > 0) {
        set_flash('danger', 'Jadwal duplikat: guru tersebut sudah mengajar di kelas, hari, dan jam yang sama.');
        header('Location: create.php');
        exit;
    }

    // Insert jadwal
    $stmtIns = $pdo->prepare("INSERT INTO jadwals (kelas_id, guru_id, mapel_id, hari, jam_mulai, jam_selesai, ruang, semester, tahun_ajaran, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
    $stmtIns->execute([$kelas_id, $guru_id, $mapel_id, $hari, $jam_mulai, $jam_selesai, $ruang, $semester, $tahun_ajaran]);

    set_flash('success', 'Jadwal pelajaran berhasil ditambahkan.');
    header('Location: index.php?semester=' . $semester . '&tahun_manual=' . urlencode($tahun_ajaran));
    exit;

} catch (Exception $e) {
    set_flash('danger', 'Terjadi kesalahan sistem: ' . $e->getMessage());
    header('Location: create.php');
    exit;
}
