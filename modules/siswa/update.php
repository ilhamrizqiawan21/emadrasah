<?php
// emadrasah/modules/siswa/update.php
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

    $id = $_POST['id'];
    try {
        $pdo->beginTransaction();

        // 1. Update Tabel Siswa
        $stmt_siswa = $pdo->prepare("UPDATE siswa SET 
            no_urut = ?, nis = ?, nisn = ?, nik = ?, nama_lengkap = ?, nama_panggilan = ?, 
            jenis_kelamin = ?, tempat_lahir = ?, tanggal_lahir = ?, kelas_id = ?, 
            tahun_pelajaran_id = ?, alamat = ?, kecamatan = ?, kabupaten_kota = ?, 
            hp = ?, status = ?, updated_at = NOW() 
            WHERE id = ?");

        $stmt_siswa->execute([
            $_POST['no_urut'], $_POST['nis'], $_POST['nisn'], $_POST['nik'], $_POST['nama_lengkap'], 
            $_POST['nama_panggilan'], $_POST['jenis_kelamin'], $_POST['tempat_lahir'], 
            $_POST['tanggal_lahir'], $_POST['kelas_id'], $_POST['tahun_pelajaran_id'], 
            $_POST['alamat'], $_POST['kecamatan'], $_POST['kabupaten_kota'], 
            $_POST['hp'], $_POST['status'], $id
        ]);

        // 2. Update/Insert Tabel Ortu
        $stmt_ortu = $pdo->prepare("UPDATE orang_tua_wali SET 
            nama_ayah = ?, pekerjaan_ayah = ?, nama_ibu = ?, pekerjaan_ibu = ?, updated_at = NOW() 
            WHERE siswa_id = ?");
        $stmt_ortu->execute([
            $_POST['nama_ayah'], $_POST['pekerjaan_ayah'], $_POST['nama_ibu'], $_POST['pekerjaan_ibu'], $id
        ]);

        // 3. Update/Insert Tabel Perkembangan
        $stmt_perkembangan = $pdo->prepare("UPDATE perkembangan_siswa SET 
            nama_madrasah_asal = ?, no_ijazah_asal = ?, updated_at = NOW() 
            WHERE siswa_id = ?");
        $stmt_perkembangan->execute([
            $_POST['nama_sekolah_asal'], $_POST['no_ijazah_asal'], $id
        ]);

        // 4. Handle Foto Baru
        if (!empty($_FILES['foto']['name'])) {
            // Ambil foto lama untuk dihapus
            $stmt_old = $pdo->prepare("SELECT foto FROM siswa WHERE id = ?");
            $stmt_old->execute([$id]);
            $old_foto = $stmt_old->fetchColumn();

            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $filename = 'foto_' . $id . '_' . time() . '.' . $ext;
            $target = __DIR__ . '/../../uploads/foto_siswa/' . $filename;
            
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $target)) {
                // Update DB & Hapus file lama
                $pdo->prepare("UPDATE siswa SET foto = ? WHERE id = ?")->execute([$filename, $id]);
                if ($old_foto && file_exists(__DIR__ . '/../../uploads/foto_siswa/' . $old_foto)) {
                    unlink(__DIR__ . '/../../uploads/foto_siswa/' . $old_foto);
                }
            }
        }

        $pdo->commit();
        set_flash('success', 'Data siswa berhasil diperbarui.');
        header("Location: show.php?id=$id");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error: " . $e->getMessage());
    }
} else {
    header('Location: index.php');
}
?>