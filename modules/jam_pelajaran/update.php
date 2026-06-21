<?php
// emadrasah/modules/jam_pelajaran/update.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . base_url('modules/jam_pelajaran/index.php'));
    exit;
}

if (!validate_csrf_request()) {
    set_flash('danger', 'Token CSRF tidak valid.');
    header('Location: ' . base_url('modules/jam_pelajaran/index.php'));
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    set_flash('danger', 'ID jam pelajaran tidak valid.');
    header('Location: ' . base_url('modules/jam_pelajaran/index.php'));
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

    if (!empty($errors)) {
        set_flash('danger', implode('<br>', $errors));
        header('Location: ' . base_url('modules/jam_pelajaran/edit.php?id=' . $id));
        exit;
    }

    // Cek duplikat: hari + sesi_ke selain id yang sedang diedit
    $stmtCek = $pdo->prepare("SELECT COUNT(*) FROM jam_pelajaran WHERE hari = ? AND sesi_ke = ? AND id != ?");
    $stmtCek->execute([$hari, $sesi, $id]);
    if ($stmtCek->fetchColumn() > 0) {
        set_flash('danger', "Sesi ke-$sesi pada hari $hari sudah ada. Gunakan sesi yang berbeda.");
        header('Location: ' . base_url('modules/jam_pelajaran/edit.php?id=' . $id));
        exit;
    }

    $stmt = $pdo->prepare("UPDATE jam_pelajaran SET hari = ?, sesi_ke = ?, jam_mulai = ?, jam_selesai = ?, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$hari, $sesi, $mulai, $selesai, $id]);

    set_flash('success', 'Sesi jam pelajaran berhasil diperbarui.');
    header('Location: ' . base_url('modules/jam_pelajaran/index.php'));
    exit;

} catch (Exception $e) {
    set_flash('danger', 'Terjadi kesalahan sistem: ' . $e->getMessage());
    header('Location: ' . base_url('modules/jam_pelajaran/edit.php?id=' . $id));
    exit;
}
