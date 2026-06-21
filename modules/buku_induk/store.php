<?php
// emadrasah/modules/buku_induk/store.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
if (!validate_csrf_request()) {
    set_flash('danger', 'Token CSRF tidak valid.');
    header('Location: ' . base_url('modules/buku_induk/create.php'));
    exit;
}

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

    // Validasi upload file (maks 2MB, tipe yang diizinkan)
    $allowed_types = ['jpg', 'jpeg', 'png', 'pdf'];
    $max_size = 2 * 1024 * 1024; // 2MB
    $upload_fields = ['foto', 'dokumen_akta', 'dokumen_kk', 'dokumen_ijazah'];
    foreach ($upload_fields as $field) {
        if (!empty($_FILES[$field]['name'])) {
            if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
                $errors[] = "Gagal upload file {$field} (kode error: {$_FILES[$field]['error']}).";
            } elseif ($_FILES[$field]['size'] > $max_size) {
                $errors[] = "File {$field} terlalu besar. Maksimal 2MB.";
            } else {
                $ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
                if (!in_array($ext, $allowed_types)) {
                    $errors[] = "File {$field} harus berformat JPG, PNG, atau PDF.";
                }
            }
        }
    }

    if (!empty($errors)) {
        $_SESSION['form_errors'] = $errors;
        $_SESSION['form_old'] = $_POST;
        set_flash('danger', 'Mohon periksa kembali input Anda.');
        header('Location: ' . base_url('modules/buku_induk/create.php'));
        exit;
    }

    // Cek duplikasi NIS / NISN / NIK
    $dup_checks = [];
    if (!empty($_POST['nis'])) {
        $c = $pdo->prepare("SELECT id FROM siswa WHERE nis = ?"); $c->execute([$_POST['nis']]);
        if ($c->fetch()) $dup_checks[] = 'NIS ' . $_POST['nis'] . ' sudah terdaftar.';
    }
    if (!empty($_POST['nisn'])) {
        $c = $pdo->prepare("SELECT id FROM siswa WHERE nisn = ?"); $c->execute([$_POST['nisn']]);
        if ($c->fetch()) $dup_checks[] = 'NISN ' . $_POST['nisn'] . ' sudah terdaftar.';
    }
    if (!empty($_POST['nik'])) {
        $c = $pdo->prepare("SELECT id FROM siswa WHERE nik = ?"); $c->execute([$_POST['nik']]);
        if ($c->fetch()) $dup_checks[] = 'NIK ' . $_POST['nik'] . ' sudah terdaftar.';
    }
    if (!empty($dup_checks)) {
        $_SESSION['form_old'] = $_POST;
        set_flash('danger', implode('<br>', $dup_checks));
        header('Location: ' . base_url('modules/buku_induk/create.php'));
        exit;
    }

    try {
        $pdo->beginTransaction();

        // 1. Menyimpan data dasar identitas siswa
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
            $_POST['kelas_id'],
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

        // 2. Menyimpan data relasional Orang Tua
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

        // 3. Menyimpan data riwayat pendidikan sebelumnya
        $stmt_perkembangan = $pdo->prepare("INSERT INTO perkembangan_siswa (
            siswa_id, nama_madrasah_asal, no_ijazah_asal, tgl_diterima, updated_at
        ) VALUES (?, ?, ?, ?, NOW())");

        $stmt_perkembangan->execute([
            $siswa_id,
            $_POST['nama_madrasah_asal'] ?: null,
            $_POST['no_ijazah_asal'] ?: null,
            $_POST['tgl_diterima'] ?: null
        ]);

        // 4. Penanganan Upload Foto Profil
        if (!empty($_FILES['foto']['name'])) {
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $filename = 'foto_' . $siswa_id . '_' . time() . '.' . $ext;
            $target = __DIR__ . '/../../uploads/foto_siswa/' . $filename;
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $target)) {
                $pdo->prepare("UPDATE siswa SET foto = ? WHERE id = ?")->execute([$filename, $siswa_id]);
            }
        }

        // 5. Penanganan Dokumen Digital (Akta/KK/Ijazah) secara batch
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
        header('Location: ' . base_url('modules/buku_induk/index.php'));
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        set_flash('danger', 'Terjadi kesalahan sistem. Silakan coba lagi. Jika masalah berlanjut, hubungi administrator.');
        header('Location: ' . base_url('modules/buku_induk/create.php'));
        exit;
    }
} else {
    header('Location: ' . base_url('modules/buku_induk/index.php'));
    exit;
}
