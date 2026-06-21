<?php
// emadrasah/modules/siswa/export_buku_induk.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil Data Lengkap (Sesuai dengan show.php)
$stmt = $pdo->prepare("
    SELECT s.*, k.nama_kelas, tp.kode as tp_kode,
           o.nama_ayah, o.pekerjaan_ayah, o.nama_ibu, o.pekerjaan_ibu,
           p.nama_sekolah_asal, p.no_ijazah_asal, p.tgl_diterima
    FROM siswa s
    LEFT JOIN kelas k ON s.kelas_id = k.id
    LEFT JOIN tahun_pelajaran tp ON s.tahun_pelajaran_id = tp.id
    LEFT JOIN orang_tua_wali o ON o.siswa_id = s.id
    LEFT JOIN perkembangan_siswa p ON p.siswa_id = s.id
    WHERE s.id = ?
");
$stmt->execute([$id]);
$s = $stmt->fetch();

if (!$s) die("Data siswa tidak ditemukan.");

// Ambil Dokumen
$stmt_doc = $pdo->prepare("SELECT * FROM siswa_dokumen WHERE siswa_id = ?");
$stmt_doc->execute([$id]);
$docs = $stmt_doc->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Buku_Induk_<?php echo $s['nis']; ?></title>
    <style>
        @page { size: A4; margin: 2cm; }
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.5; color: #000; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px double #000; padding-bottom: 10px; }
        .header h2, .header h3 { margin: 0; text-transform: uppercase; }
        .section-title { font-weight: bold; text-decoration: underline; margin-top: 20px; margin-bottom: 10px; display: block; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.no-border td { border: none; padding: 2px 5px; vertical-align: top; }
        .foto-container { float: right; width: 3cm; height: 4cm; border: 1px solid #000; text-align: center; line-height: 4cm; margin-left: 20px; }
        .foto-img { width: 100%; height: 100%; object-fit: cover; }
        .clearfix::after { content: ""; clear: both; display: table; }
        .footer { margin-top: 50px; width: 100%; }
        .footer td { text-align: center; width: 50%; }
        .no-print { background: #f8f9fa; padding: 10px; text-align: center; border-bottom: 1px solid #ddd; margin-bottom: 20px; font-family: sans-serif; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()" style="padding: 10px 20px; background: #28a745; color: #fff; border: none; cursor: pointer; font-weight: bold; border-radius: 4px;">
        <i class="fas fa-print"></i> CETAK BUKU INDUK
    </button>
</div>

<div class="header">
    <h3>LEMBAR BUKU INDUK PESERTA DIDIK</h3>
    <h2>MTs AL-IHSAN BATUJAJAR</h2>
    <p style="margin-top: 5px;">Tahun Pelajaran: <?php echo $s['tp_kode']; ?></p>
</div>

<div class="clearfix">
    <div class="foto-container">
        <?php if ($s['foto']): ?>
            <img src="<?php echo base_url('uploads/foto_siswa/' . $s['foto']); ?>" class="foto-img">
        <?php else: ?>
            FOTO 3x4
        <?php endif; ?>
    </div>

    <span class="section-title">A. KETERANGAN TENTANG DIRI PESERTA DIDIK</span>
    <table class="no-border">
        <tr><td width="30">1.</td><td width="200">Nama Lengkap</td><td width="10">:</td><td><strong><?php echo strtoupper($s['nama_lengkap']); ?></strong></td></tr>
        <tr><td>2.</td><td>Nama Panggilan</td><td>:</td><td><?php echo $s['nama_panggilan']; ?></td></tr>
        <tr><td>3.</td><td>Nomor Induk (NIS)</td><td>:</td><td><?php echo $s['nis']; ?></td></tr>
        <tr><td>4.</td><td>NISN</td><td>:</td><td><?php echo $s['nisn']; ?></td></tr>
        <tr><td>5.</td><td>Tempat, Tanggal Lahir</td><td>:</td><td><?php echo $s['tempat_lahir'] . ', ' . tgl_indo($s['tanggal_lahir']); ?></td></tr>
        <tr><td>6.</td><td>Jenis Kelamin</td><td>:</td><td><?php echo $s['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></td></tr>
        <tr><td>7.</td><td>Agama</td><td>:</td><td><?php echo $s['agama']; ?></td></tr>
        <tr><td>8.</td><td>Golongan Darah</td><td>:</td><td><?php echo $s['golongan_darah']; ?></td></tr>
    </table>
</div>

<span class="section-title">B. KETERANGAN TEMPAT TINGGAL</span>
<table class="no-border">
    <tr><td width="30">9.</td><td width="200">Alamat Lengkap</td><td width="10">:</td><td><?php echo $s['alamat_jalan']; ?></td></tr>
    <tr><td></td><td>RT / RW</td><td>:</td><td><?php echo $s['rt'] . ' / ' . $s['rw']; ?></td></tr>
    <tr><td></td><td>Desa / Kelurahan</td><td>:</td><td><?php echo $s['desa_kelurahan']; ?></td></tr>
    <tr><td></td><td>Kecamatan</td><td>:</td><td><?php echo $s['kecamatan']; ?></td></tr>
    <tr><td></td><td>Kabupaten / Kota</td><td>:</td><td><?php echo $s['kabupaten_kota']; ?></td></tr>
    <tr><td></td><td>Provinsi</td><td>:</td><td><?php echo $s['provinsi']; ?></td></tr>
    <tr><td>10.</td><td>Nomor HP</td><td>:</td><td><?php echo $s['hp']; ?></td></tr>
</table>

<span class="section-title">C. KETERANGAN ORANG TUA / WALI</span>
<table class="no-border">
    <tr><td width="30">11.</td><td width="200">Nama Ayah Kandung</td><td width="10">:</td><td><?php echo $s['nama_ayah']; ?></td></tr>
    <tr><td>12.</td><td>Pekerjaan Ayah</td><td>:</td><td><?php echo $s['pekerjaan_ayah']; ?></td></tr>
    <tr><td width="30">13.</td><td width="200">Nama Ibu Kandung</td><td width="10">:</td><td><?php echo $s['nama_ibu']; ?></td></tr>
    <tr><td>14.</td><td>Pekerjaan Ibu</td><td>:</td><td><?php echo $s['pekerjaan_ibu']; ?></td></tr>
</table>

<span class="section-title">D. KETERANGAN PENDIDIKAN SEBELUMNYA</span>
<table class="no-border">
    <tr><td width="30">15.</td><td width="200">Asal SD / MI</td><td width="10">:</td><td><?php echo $s['nama_sekolah_asal']; ?></td></tr>
    <tr><td>16.</td><td>Nomor Ijazah</td><td>:</td><td><?php echo $s['no_ijazah_asal']; ?></td></tr>
    <tr><td>17.</td><td>Tanggal Diterima di MTs</td><td>:</td><td><?php echo tgl_indo($s['tgl_diterima']); ?></td></tr>
</table>

<div class="footer">
    <table class="no-border">
        <tr>
            <td>
                Mengetahui,<br>Orang Tua / Wali<br><br><br><br>
                ( ................................. )
            </td>
            <td>
                Batujajar, <?php echo tgl_indo(date('Y-m-d')); ?><br>Kepala Madrasah<br><br><br><br>
                <strong>H. ENCEP HADIANA, S.Pd.I</strong>
            </td>
        </tr>
    </table>
</div>

</body>
</html>