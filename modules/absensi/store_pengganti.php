<?php
// emadrasah/modules/absensi/store_pengganti.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
if (!validate_csrf_request()) {
    set_flash('danger', 'Token CSRF tidak valid.');
    header('Location: ' . base_url('modules/absensi/pengganti.php?agenda_id=' . $_POST['agenda_guru_id']));
    exit;
}

    $agenda_id = $_POST['agenda_guru_id'];
    $jam_id = $_POST['jam_pelajaran_id'];
    $pengganti_id = $_POST['guru_pengganti_id'];
    $ket = input_safe($_POST['keterangan']);

    try {
        // 1. Ambil data agenda dan jam pelajaran untuk validasi
        $stmt_info = $pdo->prepare("
            SELECT a.tanggal, j.jam_mulai, j.jam_selesai, j.hari 
            FROM agenda_guru a 
            CROSS JOIN jam_pelajaran j 
            WHERE a.id = ? AND j.id = ?
        ");
        $stmt_info->execute([$agenda_id, $jam_id]);
        $info = $stmt_info->fetch();

        if (!$info) {
            set_flash('danger', 'Data agenda atau jam pelajaran tidak valid.');
            header("Location: " . base_url("modules/absensi/index.php"));
            exit;
        }

        // 2. Cek konflik dengan jadwal rutin guru pengganti
        $stmt_conflict = $pdo->prepare("
            SELECT id FROM jadwals 
            WHERE guru_id = ? AND hari = ? 
            AND (
                (jam_mulai BETWEEN ? AND ?) OR 
                (jam_selesai BETWEEN ? AND ?) OR 
                (? <= jam_mulai AND ? >= jam_selesai)
            )
        ");
        $stmt_conflict->execute([
            $pengganti_id, 
            $info['hari'], 
            $info['jam_mulai'], $info['jam_selesai'],
            $info['jam_mulai'], $info['jam_selesai'],
            $info['jam_mulai'], $info['jam_selesai']
        ]);

        if ($stmt_conflict->fetch()) {
            set_flash('danger', 'Gagal: Guru infaler memiliki jadwal mengajar rutin di jam tersebut.');
            header("Location: " . base_url("modules/absensi/pengganti.php?agenda_id=$agenda_id"));
            exit;
        }

        // 3. Cek apakah guru pengganti sudah ditugaskan di sesi yang sama pada tanggal tersebut
        $stmt_cek = $pdo->prepare("
            SELECT gp.id 
            FROM guru_pengganti gp
            JOIN agenda_guru ag ON gp.agenda_guru_id = ag.id
            WHERE ag.tanggal = ? AND gp.jam_pelajaran_id = ? AND gp.guru_pengganti_id = ?
        ");
        $stmt_cek->execute([$info['tanggal'], $jam_id, $pengganti_id]);
        
        if ($stmt_cek->fetch()) {
            set_flash('danger', 'Gagal: Guru ini sudah ditugaskan sebagai infaler di sesi tersebut.');
        } else {
            $stmt = $pdo->prepare("INSERT INTO guru_pengganti (agenda_guru_id, jam_pelajaran_id, guru_pengganti_id, keterangan, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
            $stmt->execute([$agenda_id, $jam_id, $pengganti_id, $ket]);
            set_flash('success', 'Guru infaler berhasil ditugaskan.');
        }

        header("Location: " . base_url("modules/absensi/pengganti.php?agenda_id=$agenda_id"));
        exit;
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}
?>