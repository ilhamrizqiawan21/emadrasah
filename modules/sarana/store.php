<?php
// emadrasah/modules/sarana/store.php
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
        $kode = input_safe($_POST['kode_sarana']);
        $nama = input_safe($_POST['nama_sarana']);
        $kat = $_POST['kategori_id'];
        $jml = $_POST['jumlah'];
        $kondisi = $_POST['kondisi'];
        $lokasi = input_safe($_POST['lokasi_ruang']);
        $tahun = $_POST['tahun_pengadaan'];
        $spek = input_safe($_POST['spesifikasi']);

        $foto = null;
        if (!empty($_FILES['foto']['name'])) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
            if (!in_array($_FILES['foto']['type'], $allowed_types)) {
                throw new Exception("Format gambar tidak didukung.");
            }
            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $filename = 'BRG_' . time() . '.' . $ext;
            $target = __DIR__ . '/../../uploads/sarana/' . $filename;
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $target)) {
                $foto = $filename;
            }
        }

        $stmt = $pdo->prepare("INSERT INTO sarana_prasarana (kode_sarana, nama_sarana, kategori_id, jumlah, stok_tersedia, kondisi, lokasi_ruang, tahun_pengadaan, spesifikasi, foto, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([$kode, $nama, $kat, $jml, $jml, $kondisi, $lokasi, $tahun, $spek, $foto]);

        set_flash('success', 'Data aset berhasil ditambahkan.');
        header('Location: index.php');
        exit;
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}
?>