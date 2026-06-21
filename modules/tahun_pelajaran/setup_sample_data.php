<?php
// emadrasah/modules/tahun_pelajaran/setup_sample_data.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Check if data already exists
$existingCount = $pdo->query("SELECT COUNT(*) FROM tahun_pelajaran")->fetchColumn();

if ($existingCount == 0) {
    // Insert sample data
    $data = [
        ['kode' => '2024/2025', 'nama' => 'Tahun Pelajaran 2024/2025', 'is_aktif' => 0],
        ['kode' => '2025/2026', 'nama' => 'Tahun Pelajaran 2025/2026', 'is_aktif' => 1],
        ['kode' => '2026/2027', 'nama' => 'Tahun Pelajaran 2026/2027', 'is_aktif' => 0],
    ];
    
    $stmt = $pdo->prepare("INSERT INTO tahun_pelajaran (kode, nama, is_aktif) VALUES (?, ?, ?)");
    
    foreach ($data as $row) {
        $stmt->execute([$row['kode'], $row['nama'], $row['is_aktif']]);
    }
    
    set_flash('success', 'Sample data tahun pelajaran berhasil ditambahkan!');
} else {
    set_flash('info', 'Data tahun pelajaran sudah ada.');
}

// Redirect ke halaman jadwal menggunakan base_url
$referer = $_SERVER['HTTP_REFERER'] ?? '';
if (strpos($referer, 'cetak_jadwal') !== false) {
    header('Location: ' . base_url('modules/jadwal/cetak_jadwal.php'));
} else {
    header('Location: ' . base_url('modules/jadwal/index.php'));
}
exit;
?>
