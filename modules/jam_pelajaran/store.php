<?php
// emadrasah/modules/jam_pelajaran/store.php
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
    $hari   = isset($_POST['hari']) ? trim($_POST['hari']) : '';
    $sesi   = isset($_POST['sesi_ke']) ? (int)$_POST['sesi_ke'] : 0;
    $mulai  = isset($_POST['jam_mulai']) ? trim($_POST['jam_mulai']) : '';
    $selesai = isset($_POST['jam_selesai']) ? trim($_POST['jam_selesai']) : '';

    // Validasi required
    $errors = [];
    if (empty($hari)) $errors[] = 'Hari wajib dipilih.';
    if ($sesi <= 0) $errors[] = 'Sesi ke- wajib diisi (minimal 1).';
    if (empty($mulai)) $errors[] = 'Jam mulai wajib diisi.';
    if (empty($selesai)) $errors[] = 'Jam selesai wajib diisi.';

    // Validasi jam_selesai > jam_mulai
    if (!empty($mulai) && !empty($selesai)) {
        if (strtotime($selesai) <= strtotime($mulai)) {
            $errors[] = 'Jam selesai harus lebih besar dari jam mulai.';
        }
    }

    // Validasi hari yang valid
    $valid_hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
    if (!empty($hari) && !in_array($hari, $valid_hari)) {
        $errors[] = 'Hari tidak valid.';
    }

    if (!empty($errors)) {
        set_flash('danger', implode('<br>', $errors));
        header('Location: create.php');
        exit;
    }

    // Cek duplikat: hari + sesi_ke yang sama
    $stmtCek = $pdo->prepare("SELECT COUNT(*) FROM jam_pelajaran WHERE hari = ? AND sesi_ke = ?");
    $stmtCek->execute([$hari, $sesi]);
    if ($stmtCek->fetchColumn() > 0) {
        set_flash('danger', "Sesi ke-$sesi pada hari $hari sudah ada. Gunakan sesi yang berbeda.");
        header('Location: create.php');
        exit;
    }

    // Insert data
    $stmt = $pdo->prepare("INSERT INTO jam_pelajaran (hari, sesi_ke, jam_mulai, jam_selesai, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
    $stmt->execute([$hari, $sesi, $mulai, $selesai]);

    set_flash('success', 'Sesi jam pelajaran berhasil ditambahkan.');
    header('Location: index.php');
    exit;

} catch (Exception $e) {
    set_flash('danger', 'Terjadi kesalahan sistem: ' . $e->getMessage());
    header('Location: create.php');
    exit;
}
