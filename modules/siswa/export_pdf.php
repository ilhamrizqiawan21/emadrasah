<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

require_login();

use Dompdf\Dompdf;
use Dompdf\Options;

$stmt = $pdo->query("SELECT s.*, k.nama_kelas FROM siswa s LEFT JOIN kelas k ON s.kelas_id = k.id ORDER BY s.no_urut ASC");
$siswa_list = $stmt->fetchAll();

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);

$html = '
<style>
    body { font-family: sans-serif; font-size: 12px; }
    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
    th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    .header { text-align: center; margin-bottom: 30px; }
    .title { font-size: 18px; font-weight: bold; margin-bottom: 5px; }
</style>
<div class="header">
    <div class="title">LAPORAN DATA SISWA</div>
    <div>Sistem Informasi e-Madrasah — MTs Al-Ihsan</div>
</div>
<table>
    <thead>
        <tr>
            <th>No</th>
            <th>NIS/NISN</th>
            <th>Nama Lengkap</th>
            <th>JK</th>
            <th>Kelas</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>';

foreach ($siswa_list as $s) {
    $html .= '
        <tr>
            <td>' . $s['no_urut'] . '</td>
            <td>' . $s['nis'] . ' / ' . $s['nisn'] . '</td>
            <td>' . $s['nama_lengkap'] . '</td>
            <td>' . $s['jenis_kelamin'] . '</td>
            <td>' . $s['nama_kelas'] . '</td>
            <td>' . $s['status'] . '</td>
        </tr>';
}

$html .= '</tbody></table>';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Data_Siswa_eMadrasah.pdf", ["Attachment" => false]);
exit;
