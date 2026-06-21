<?php
// emadrasah/modules/sarana/update.php
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
        $kode = input_safe($_POST['kode_sarana']);
        $nama = input_safe($_POST['nama_sarana']);
        $kat = $_POST['kategori_id'];
        $jml = $_POST['jumlah'];
        $kondisi = $_POST['kondisi'];
        $lokasi = input_safe($_POST['lokasi_ruang']);
        $tahun = $_POST['tahun_pengadaan'];
        $spek = input_safe($_POST['spesifikasi']);

        // Handle Foto
        if (!empty($_FILES['foto']['name'])) {
            // Ambil foto lama untuk dihapus
            $stmt_old = $pdo->prepare("SELECT foto FROM sarana_prasarana WHERE id = ?");
            $stmt_old->execute([$id]);
            $old_foto = $stmt_old->fetchColumn();

            $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $filename = 'BRG_' . time() . '.' . $ext;
            $target = __DIR__ . '/../../uploads/sarana/' . $filename;
            
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $target)) {
                $pdo->prepare("UPDATE sarana_prasarana SET foto = ? WHERE id = ?")->execute([$filename, $id]);
                if ($old_foto && file_exists(__DIR__ . '/../../uploads/sarana/' . $old_foto)) {
                    unlink(__DIR__ . '/../../uploads/sarana/' . $old_foto);
                }
            }
        }

        // Ambil data lama untuk hitung selisih stok
        $stmt_old = $pdo->prepare("SELECT jumlah, stok_tersedia FROM sarana_prasarana WHERE id = ?");
        $stmt_old->execute([$id]);
        $old_data = $stmt_old->fetch();
        
        $selisih = $jml - $old_data['jumlah'];
        $new_stok_tersedia = $old_data['stok_tersedia'] + $selisih;

        $stmt = $pdo->prepare("UPDATE sarana_prasarana SET kode_sarana = ?, nama_sarana = ?, kategori_id = ?, jumlah = ?, stok_tersedia = ?, kondisi = ?, lokasi_ruang = ?, tahun_pengadaan = ?, spesifikasi = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$kode, $nama, $kat, $jml, $new_stok_tersedia, $kondisi, $lokasi, $tahun, $spek, $id]);

        set_flash('success', 'Data aset berhasil diperbarui.');
        header('Location: index.php');
        exit;
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}
?>