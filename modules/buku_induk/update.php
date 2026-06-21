<?php
// emadrasah/modules/buku_induk/update.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
if (!validate_csrf_request()) {
    set_flash('danger', 'Token CSRF tidak valid.');
    header('Location: ' . base_url('modules/buku_induk/edit.php?id=' . $_POST['id']));
    exit;
}

    $id = $_POST['id'];

    // Validasi field wajib
    $errors = [];
    if (empty($_POST['nama_lengkap']) || trim($_POST['nama_lengkap']) === '') {
        $errors[] = 'Nama Lengkap wajib diisi.';
    }
    if (empty($_POST['nis']) || trim($_POST['nis']) === '') {
        $errors[] = 'NIS wajib diisi.';
    }
    if (empty($_POST['kelas_id'])) {
        $errors[] = 'Kelas wajib dipilih.';
    }

    if (!empty($errors)) {
        set_flash('danger', implode('<br>', $errors));
        header('Location: ' . base_url('modules/buku_induk/edit.php?id=' . $id));
        exit;
    }

    try {
        $pdo->beginTransaction();

        $stmt_siswa = $pdo->prepare("UPDATE siswa SET
            no_urut = ?, nis = ?, nisn = ?, nik = ?, nama_lengkap = ?, nama_panggilan = ?,
            jenis_kelamin = ?, tempat_lahir = ?, tanggal_lahir = ?, agama = ?, golongan_darah = ?,
            kelas_id = ?, tahun_pelajaran_id = ?, alamat = ?, rt = ?, rw = ?, desa_kelurahan = ?,
            kecamatan = ?, kabupaten_kota = ?, provinsi = ?, hp = ?, status = ?, updated_at = NOW()
            WHERE id = ?");

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
            $_POST['agama'] ?: null,
            $_POST['golongan_darah'] ?: null,
            $_POST['kelas_id'],
            $_POST['tahun_pelajaran_id'] ?: null,
            $_POST['alamat'] ?: null,
            $_POST['rt'] ?: null,
            $_POST['rw'] ?: null,
            $_POST['desa_kelurahan'] ?: null,
            $_POST['kecamatan'] ?: null,
            $_POST['kabupaten_kota'] ?: null,
            $_POST['provinsi'] ?: null,
            $_POST['hp'] ?: null,
            $_POST['status'] ?: 'Aktif',
            $id
        ]);

        $stmt_ortu = $pdo->prepare("UPDATE orang_tua_wali SET
            nama_ayah = ?, pekerjaan_ayah = ?, nama_ibu = ?, pekerjaan_ibu = ?, updated_at = NOW()
            WHERE siswa_id = ?");
        $stmt_ortu->execute([
            $_POST['nama_ayah'] ?: null,
            $_POST['pekerjaan_ayah'] ?: null,
            $_POST['nama_ibu'] ?: null,
            $_POST['pekerjaan_ibu'] ?: null,
            $id
        ]);

        $stmt_perkembangan = $pdo->prepare("UPDATE perkembangan_siswa SET
            nama_madrasah_asal = ?, no_ijazah_asal = ?, tgl_diterima = ?, updated_at = NOW()
            WHERE siswa_id = ?");
        $stmt_perkembangan->execute([
            $_POST['nama_madrasah_asal'] ?: null,
            $_POST['no_ijazah_asal'] ?: null,
            $_POST['tgl_diterima'] ?: null,
            $id
        ]);

        if (!empty($_FILES['foto']['name'])) {
            $stmt_old = $pdo->prepare("SELECT foto FROM siswa WHERE id = ?");
            $stmt_old->execute([$id]);
            $old_foto = $stmt_old->fetchColumn();

            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $filename = 'foto_' . $id . '_' . time() . '.' . $ext;
            $target = __DIR__ . '/../../uploads/foto_siswa/' . $filename;

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $target)) {
                $pdo->prepare("UPDATE siswa SET foto = ? WHERE id = ?")->execute([$filename, $id]);
                if ($old_foto && file_exists(__DIR__ . '/../../uploads/foto_siswa/' . $old_foto)) {
                    unlink(__DIR__ . '/../../uploads/foto_siswa/' . $old_foto);
                }
            }
        }

        $pdo->commit();
        set_flash('success', 'Data siswa berhasil diperbarui.');
        header("Location: " . base_url("modules/buku_induk/show.php?id=$id"));
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        set_flash('danger', 'Terjadi kesalahan sistem. Silakan coba lagi. Jika masalah berlanjut, hubungi administrator.');
        header('Location: ' . base_url('modules/buku_induk/edit.php?id=' . $id));
        exit;
    }
}

header('Location: ' . base_url('modules/buku_induk/index.php'));
exit;
