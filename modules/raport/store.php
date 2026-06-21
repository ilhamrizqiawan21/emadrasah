<?php
// emadrasah/modules/raport/store.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
if (!validate_csrf_request()) {
    set_flash('danger', 'Token CSRF tidak valid.');
    header('Location: ' . base_url('modules/raport/manage.php?id=' . $_POST['siswa_id'] . '&tahun_pelajaran_id=' . $_POST['tahun_pelajaran_id'] . '&semester=' . $_POST['semester']));
    exit;
}

    $siswa_id = $_POST['siswa_id'];
    $tp_id = $_POST['tahun_pelajaran_id'];
    $semester = $_POST['semester'];

    try {
        $pdo->beginTransaction();

        foreach ($_POST['nilai'] as $mapel_id => $data) {
            $angka = $data['angka'];
            $capaian = $data['capaian'];

            // Jika nilai kosong, kita bisa memilih untuk mengabaikan atau menghapus data lama.
            // Di sini kita gunakan logika: jika ada input angka, kita simpan/update.
            if ($angka !== '') {
                // Cek apakah sudah ada
                $stmt_cek = $pdo->prepare("SELECT id FROM raport_nilai WHERE siswa_id = ? AND tahun_pelajaran_id = ? AND semester = ? AND mapel_id = ?");
                $stmt_cek->execute([$siswa_id, $tp_id, $semester, $mapel_id]);
                $exists = $stmt_cek->fetch();

                if ($exists) {
                    $stmt_upd = $pdo->prepare("UPDATE raport_nilai SET nilai_akhir = ?, capaian_kompetensi = ?, updated_at = NOW() WHERE id = ?");
                    $stmt_upd->execute([$angka, $capaian, $exists['id']]);
                } else {
                    $stmt_ins = $pdo->prepare("INSERT INTO raport_nilai (siswa_id, tahun_pelajaran_id, semester, mapel_id, nilai_akhir, capaian_kompetensi, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
                    $stmt_ins->execute([$siswa_id, $tp_id, $semester, $mapel_id, $angka, $capaian]);
                }
            }
        }

        $pdo->commit();
        set_flash('success', 'Arsip nilai raport berhasil disimpan.');
        header("Location: " . base_url("modules/raport/manage.php?id=$siswa_id&tahun_pelajaran_id=$tp_id&semester=$semester"));
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
} else {
    header('Location: ' . base_url('modules/raport/index.php'));
}
?>