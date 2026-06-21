<?php
// emadrasah/modules/siswa/store.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
if (!validate_csrf_request()) {
    set_flash('danger', 'Token CSRF tidak valid.');
    $redirect = 'index.php';
    if (basename($_SERVER['PHP_SELF']) == 'store.php') $redirect = 'create.php';
    if (basename($_SERVER['PHP_SELF']) == 'update.php' && isset($_POST['id'])) $redirect = 'edit.php?id=' . $_POST['id'];
    header('Location: ' . $redirect);
    exit;
}

    try {
        $pdo->beginTransaction();

        // 1. Simpan Data ke Tabel Siswa
        $stmt_siswa = $pdo->prepare("INSERT INTO siswa (
            no_urut, nis, nisn, nik, nama_lengkap, nama_panggilan, jenis_kelamin, 
            tempat_lahir, tanggal_lahir, agama, golongan_darah, kelas_id, 
            tahun_pelajaran_id, alamat, rt, rw, desa_kelurahan, kecamatan, 
            kabupaten_kota, provinsi, hp, status, created_at, updated_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Aktif', NOW(), NOW()
        )");

        $stmt_siswa->execute([
            $_POST['no_urut'] ?: null,
            $_POST['nis'],
            $_POST['nisn'] ?: null,
            $_POST['nik'] ?: null,
            $_POST['nama_lengkap'],
            $_POST['nama_panggilan'] ?: null,
            $_POST['jenis_kelamin'],
            $_POST['tempat_lahir'] ?: null,
            $_POST['tanggal_lahir'] ?: null,
            $_POST['agama'] ?: 'Islam',
            $_POST['golongan_darah'] ?: 'Tidak Tahu',
            $_POST['kelas_id'] ?: null,
            $_POST['tahun_pelajaran_id'] ?: null,
            $_POST['alamat'] ?: null,
            $_POST['rt'] ?: null,
            $_POST['rw'] ?: null,
            $_POST['desa_kelurahan'] ?: null,
            $_POST['kecamatan'] ?: null,
            $_POST['kabupaten_kota'] ?: null,
            $_POST['provinsi'] ?: null,
            $_POST['hp'] ?: null
        ]);

        $siswa_id = $pdo->lastInsertId();

        // 2. Simpan Data ke Tabel Orang Tua Wali
        $stmt_ortu = $pdo->prepare("INSERT INTO orang_tua_wali (
            siswa_id, nama_ayah, pekerjaan_ayah, nama_ibu, pekerjaan_ibu, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");

        $stmt_ortu->execute([
            $siswa_id,
            $_POST['nama_ayah'] ?: null,
            $_POST['pekerjaan_ayah'] ?: null,
            $_POST['nama_ibu'] ?: null,
            $_POST['pekerjaan_ibu'] ?: null
        ]);

        // 3. Simpan Data ke Tabel Perkembangan Siswa
        $stmt_perkembangan = $pdo->prepare("INSERT INTO perkembangan_siswa (
            siswa_id, nama_madrasah_asal, no_ijazah_asal, tgl_diterima, updated_at
        ) VALUES (?, ?, ?, ?, NOW())");

        $stmt_perkembangan->execute([
            $siswa_id,
            $_POST['nama_sekolah_asal'] ?: null,
            $_POST['no_ijazah_asal'] ?: null,
            $_POST['tgl_diterima'] ?: null
        ]);

        // 4. Handle Upload Foto (Opsional)
        if (!empty($_FILES['foto']['name'])) {
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $filename = 'foto_' . $siswa_id . '_' . time() . '.' . $ext;
            $target = __DIR__ . '/../../uploads/foto_siswa/' . $filename;
            
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $target)) {
                $pdo->prepare("UPDATE siswa SET foto = ? WHERE id = ?")->execute([$filename, $siswa_id]);
            }
        }

        // 5. Handle Multi-Dokumen (Akta, KK, Ijazah)
        $dokumen_types = ['dokumen_akta' => 'Akta Kelahiran', 'dokumen_kk' => 'Kartu Keluarga', 'dokumen_ijazah' => 'Ijazah'];
        foreach ($dokumen_types as $input_name => $jenis) {
            if (!empty($_FILES[$input_name]['name'])) {
                $ext = pathinfo($_FILES[$input_name]['name'], PATHINFO_EXTENSION);
                $filename = 'DOC_' . $siswa_id . '_' . $input_name . '_' . time() . '.' . $ext;
                $target = __DIR__ . '/../../uploads/dokumen_siswa/' . $filename;
                
                if (move_uploaded_file($_FILES[$input_name]['tmp_name'], $target)) {
                    $stmt_doc = $pdo->prepare("INSERT INTO siswa_dokumen (siswa_id, jenis_dokumen, file_path, nama_file, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
                    $stmt_doc->execute([$siswa_id, $jenis, $filename, $_FILES[$input_name]['name']]);
                }
            }
        }

        $pdo->commit();
        set_flash('success', 'Data siswa berhasil ditambahkan ke Buku Induk.');
        header('Location: index.php');
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
} else {
    header('Location: index.php');
}
?>