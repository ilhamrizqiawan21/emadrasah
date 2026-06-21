<?php
// emadrasah/modules/absensi/rekap_print.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('m');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

// Ambil Data Absensi Tergroup per Guru (Sesuai dengan rekap.php)
$stmt = $pdo->prepare("
    SELECT g.nama, g.kode,
           COUNT(CASE WHEN a.status = 'hadir' THEN 1 END) as hadir,
           COUNT(CASE WHEN a.status = 'izin' THEN 1 END) as izin,
           COUNT(CASE WHEN a.status = 'sakit' THEN 1 END) as sakit,
           COUNT(CASE WHEN a.status = 'alpha' THEN 1 END) as alpha
    FROM gurus g
    LEFT JOIN agenda_guru a ON a.guru_id = g.id AND MONTH(a.tanggal) = ? AND YEAR(a.tanggal) = ?
    GROUP BY g.id
    ORDER BY g.nama ASC
");
$stmt->execute([$bulan, $tahun]);
$rekap_list = $stmt->fetchAll();

$nama_bulan = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap_Absensi_<?php echo $bulan . '_' . $tahun; ?></title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11pt; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h2 { margin: 0; }
        .report-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .report-table th, .report-table td { border: 1px solid #000; padding: 8px; text-align: left; }
        .report-table th { background-color: #f2f2f2; text-align: center; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .footer-sign { margin-top: 50px; width: 100%; }
        .footer-sign td { width: 50%; text-align: center; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

<div class="no-print" style="background: #e9ecef; padding: 15px; margin-bottom: 20px; border-radius: 5px; text-align: center;">
    <button onclick="window.print()" style="padding: 10px 25px; background: #0d6efd; color: #fff; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">
        <i class="fas fa-print"></i> CETAK LAPORAN
    </button>
    <p style="font-size: 0.85rem; margin-top: 5px; color: #666;">Gunakan browser Chrome/Edge untuk hasil PDF terbaik.</p>
</div>

<div class="header">
    <h2>REKAPITULASI ABSENSI GURU</h2>
    <h3>MTs AL-IHSAN BATUJAJAR</h3>
    <p>Periode: <?php echo $nama_bulan[$bulan] . ' ' . $tahun; ?></p>
</div>

<table class="report-table">
    <thead>
        <tr>
            <th style="width: 60px;">Kode</th>
            <th>Nama Guru</th>
            <th style="width: 80px;">Hadir</th>
            <th style="width: 80px;">Izin</th>
            <th style="width: 80px;">Sakit</th>
            <th style="width: 80px;">Alpha</th>
            <th style="width: 80px;">Total</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rekap_list as $r): ?>
        <?php $total = $r['hadir'] + $r['izin'] + $r['sakit'] + $r['alpha']; ?>
        <tr>
            <td class="text-center fw-bold"><?php echo $r['kode']; ?></td>
            <td class="fw-bold"><?php echo $r['nama']; ?></td>
            <td class="text-center"><?php echo $r['hadir']; ?></td>
            <td class="text-center"><?php echo $r['izin']; ?></td>
            <td class="text-center"><?php echo $r['sakit']; ?></td>
            <td class="text-center"><?php echo $r['alpha']; ?></td>
            <td class="text-center fw-bold" style="background: #f9f9f9;"><?php echo $total; ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<table class="footer-sign">
    <tr>
        <td>
            <br>Mengetahui,<br>Kepala MTs. Al-Ihsan Batujajar<br><br><br><br><br>
            <strong>Dra. Hj. Lina Nurhasanah</strong>
        </td>
        <td>
            Batujajar, <?php echo tgl_indo(date('Y-m-d')); ?><br>Staff Tata Usaha<br><br><br><br><br>
            ( ................................. )
        </td>
    </tr>
</table>

</body>
</html>