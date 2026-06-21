<?php
// emadrasah/modules/raport/export-pdf.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Catatan: Di Native PHP Hosting, kita akan menggunakan cara termudah: 
// Meng-generate tampilan HTML khusus cetak yang otomatis memicu print browser.
// Ini jauh lebih cepat dan kompatibel dengan semua shared hosting tanpa perlu library berat.

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$tp_id = isset($_GET['tahun_pelajaran_id']) ? $_GET['tahun_pelajaran_id'] : null;
$semester = isset($_GET['semester']) ? $_GET['semester'] : 1;

// 1. Ambil Data Siswa & Sekolah
$query_siswa = "SELECT s.*, k.nama_kelas, tp.kode as tp_kode 
                       FROM siswa s 
                       LEFT JOIN kelas k ON s.kelas_id = k.id ";
if ($tp_id) {
    $query_siswa .= " LEFT JOIN tahun_pelajaran tp ON tp.id = ? WHERE s.id = ?";
    $stmt = $pdo->prepare($query_siswa);
    $stmt->execute([$tp_id, $id]);
} else {
    $query_siswa .= " LEFT JOIN tahun_pelajaran tp ON s.tahun_pelajaran_id = tp.id WHERE s.id = ?";
    $stmt = $pdo->prepare($query_siswa);
    $stmt->execute([$id]);
}
$siswa = $stmt->fetch();

if (!$siswa) die("Data tidak ditemukan.");

// 2. Ambil Daftar Mapel & Nilai
$stmt_nilai = $pdo->prepare("SELECT m.nama_mapel, m.kode_mapel, r.nilai_akhir, r.capaian_kompetensi 
                             FROM mapels m 
                             LEFT JOIN raport_nilai r ON r.mapel_id = m.id 
                                AND r.siswa_id = ? 
                                AND r.tahun_pelajaran_id = ? 
                                AND r.semester = ?
                             ORDER BY m.nama_mapel ASC");
$stmt_nilai->execute([$id, $tp_id, $semester]);
$nilai_list = $stmt_nilai->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Raport_<?php echo $siswa['nis']; ?>_S<?php echo $semester; ?></title>
    <style>
        body { font-family: 'Times New Roman', serif; font-size: 12pt; line-height: 1.4; padding: 40px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px double #000; padding-bottom: 10px; }
        .header h2 { margin: 0; text-transform: uppercase; }
        .info-table { width: 100%; margin-bottom: 20px; }
        .info-table td { vertical-align: top; }
        .report-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .report-table th, .report-table td { border: 1px solid #000; padding: 8px; text-align: left; }
        .report-table th { background-color: #f2f2f2; text-align: center; }
        .text-center { text-align: center; }
        .footer-sign { margin-top: 50px; width: 100%; }
        .footer-sign td { width: 33%; text-align: center; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

<div class="no-print" style="background: #fff3cd; padding: 15px; margin-bottom: 20px; border: 1px solid #ffeeba; text-align: center;">
    <button onclick="window.print()" style="padding: 10px 20px; background: #28a745; color: #fff; border: none; cursor: pointer; font-weight: bold;">
        <i class="fas fa-print"></i> CETAK RAPORT SEKARANG
    </button>
    <p style="font-size: 0.9rem; margin-top: 5px;">Tips: Atur Margin ke 'None' di pengaturan print browser untuk hasil terbaik.</p>
</div>

<div class="header">
    <h2>LAPORAN HASIL BELAJAR (RAPORT)</h2>
    <h3>MTs AL-IHSAN BATUJAJAR</h3>
</div>

<table class="info-table">
    <tr>
        <td style="width: 150px;">Nama Peserta Didik</td><td style="width: 10px;">:</td><td style="font-weight: bold;"><?php echo strtoupper($siswa['nama_lengkap']); ?></td>
        <td style="width: 150px;">Kelas</td><td style="width: 10px;">:</td><td><?php echo $siswa['nama_kelas']; ?></td>
    </tr>
    <tr>
        <td>NIS / NISN</td><td>:</td><td><?php echo $siswa['nis']; ?> / <?php echo $siswa['nisn']; ?></td>
        <td>Semester</td><td>:</td><td><?php echo $semester == 1 ? '1 (Ganjil)' : '2 (Genap)'; ?></td>
    </tr>
    <tr>
        <td>Madrasah</td><td>:</td><td>MTs Al-Ihsan Batujajar</td>
        <td>Tahun Pelajaran</td><td>:</td><td><?php echo $siswa['tp_kode']; ?></td>
    </tr>
</table>

<table class="report-table">
    <thead>
        <tr>
            <th style="width: 40px;">No</th>
            <th style="width: 250px;">Mata Pelajaran</th>
            <th style="width: 80px;">Nilai Akhir</th>
            <th>Capaian Kompetensi</th>
        </tr>
    </thead>
    <tbody>
        <?php $no=1; foreach($nilai_list as $n): ?>
        <tr>
            <td class="text-center"><?php echo $no++; ?></td>
            <td><?php echo $n['nama_mapel']; ?></td>
            <td class="text-center" style="font-weight: bold;"><?php echo $n['nilai_akhir'] ?: '-'; ?></td>
            <td style="font-size: 10pt;"><?php echo $n['capaian_kompetensi'] ?: 'Belum ada deskripsi.'; ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<table class="footer-sign">
    <tr>
        <td>
            Mengetahui,<br>Orang Tua/Wali<br><br><br><br>
            ( ................................. )
        </td>
        <td></td>
        <td>
            Batujajar, <?php echo tgl_indo(date('Y-m-d')); ?><br>Wali Kelas<br><br><br><br>
            ( ................................. )
        </td>
    </tr>
    <tr>
        <td colspan="3"><br>
            Mengetahui,<br>Kepala Madrasah<br><br><br><br>
            <strong>H. ENCEP HADIANA, S.Pd.I</strong><br>
            NIP. -
        </td>
    </tr>
</table>

<script>
    // Otomatis buka dialog print saat halaman dimuat (opsional)
    // window.onload = function() { window.print(); }
</script>

</body>
</html>