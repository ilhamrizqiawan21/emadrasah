<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/auth.php';

require_login();

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$stmt = $pdo->query("SELECT s.*, k.nama_kelas FROM siswa s LEFT JOIN kelas k ON s.kelas_id = k.id ORDER BY s.no_urut ASC");
$siswa_list = $stmt->fetchAll();

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Header
$headers = ['No. Urut', 'NIS', 'NISN', 'Nama Lengkap', 'Jenis Kelamin', 'Kelas', 'Status'];
foreach ($headers as $key => $title) {
    $sheet->setCellValueByColumnAndRow($key + 1, 1, $title);
}

// Data
$row = 2;
foreach ($siswa_list as $s) {
    $sheet->setCellValueByColumnAndRow(1, $row, $s['no_urut']);
    $sheet->setCellValueByColumnAndRow(2, $row, $s['nis']);
    $sheet->setCellValueByColumnAndRow(3, $row, $s['nisn']);
    $sheet->setCellValueByColumnAndRow(4, $row, $s['nama_lengkap']);
    $sheet->setCellValueByColumnAndRow(5, $row, $s['jenis_kelamin']);
    $sheet->setCellValueByColumnAndRow(6, $row, $s['nama_kelas']);
    $sheet->setCellValueByColumnAndRow(7, $row, $s['status']);
    $row++;
}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Data_Siswa_eMadrasah.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
