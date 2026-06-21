<?php
// emadrasah/modules/jadwal/export_pdf.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

// Ambil parameter filter
$semester = isset($_GET['semester']) ? (int)$_GET['semester'] : 1;
$tahun_manual = isset($_GET['tahun_manual']) ? trim($_GET['tahun_manual']) : '';

// Jika tahun_manual kosong, ambil dari tahun aktif jika ada
if (empty($tahun_manual)) {
    $tapel = $pdo->query("SELECT * FROM tahun_pelajaran WHERE is_aktif = 1 LIMIT 1")->fetch();
    $tahun_manual = $tapel ? $tapel['kode'] : '2025/2026';
}
$tapel_name = $tahun_manual;

// Ambil data Kelas
$kelas = $pdo->query("SELECT * FROM kelas ORDER BY nama_kelas ASC")->fetchAll();

// Ambil data Jam Pelajaran (Urut Hari & Sesi)
$orderHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
$jamPelajaranRaw = $pdo->query("SELECT DISTINCT hari, sesi_ke, jam_mulai, jam_selesai FROM jam_pelajaran ORDER BY sesi_ke ASC")->fetchAll();

// Sortir jamPelajaran berdasarkan urutan hari yang benar
usort($jamPelajaranRaw, function($a, $b) use ($orderHari) {
    $posA = array_search($a['hari'], $orderHari);
    $posB = array_search($b['hari'], $orderHari);
    if ($posA === false) $posA = 99;
    if ($posB === false) $posB = 99;
    if ($posA == $posB) return $a['sesi_ke'] - $b['sesi_ke'];
    return $posA - $posB;
});

// Ambil SEMUA Guru Aktif, urutkan berdasarkan KODE
$allGurus = $pdo->query("SELECT id, kode, nama, bidang_studi FROM gurus WHERE status IN ('aktif', 'Aktif') ORDER BY CAST(kode AS UNSIGNED), kode ASC")->fetchAll();

// Ambil data Mapel untuk identifikasi mapel 4 jam
$mapels = $pdo->query("SELECT id, nama_mapel FROM mapels")->fetchAll();
$fourHourSubjects = [
    'Bahasa Indonesia', 'Bahasa Inggris', 'Bahasa Arab', 'Matematika', 'IPA', 'IPA Terpadu'
];
$fourHourMapelIds = [];
foreach ($mapels as $m) {
    if (in_array($m['nama_mapel'], $fourHourSubjects)) {
        $fourHourMapelIds[$m['id']] = true;
    }
}

// Ambil Jadwal dengan filter semester dan tahun
$stmtJadwal = $pdo->prepare("
    SELECT j.*, g.kode as guru_kode, g.nama as guru_nama, m.nama_mapel, k.nama_kelas
    FROM jadwals j 
    LEFT JOIN gurus g ON j.guru_id = g.id
    LEFT JOIN mapels m ON j.mapel_id = m.id
    LEFT JOIN kelas k ON j.kelas_id = k.id
    WHERE j.semester = ? AND j.tahun_ajaran = ?
");
$stmtJadwal->execute([$semester, $tahun_manual]);
$jadwals = $stmtJadwal->fetchAll();

// Inisialisasi Ringkasan Guru
$teacherSummary = [];
foreach ($allGurus as $g) {
    $teacherSummary[$g['id']] = [
        'nama' => $g['nama'],
        'kode' => $g['kode'],
        'bidang' => $g['bidang_studi'],
        'mapels' => [],
        'total_jp_calculated' => 0 // New field for calculated JP
    ];
}

foreach ($jadwals as $j) {
    if (!$j['guru_id']) continue;
    $gid = $j['guru_id'];

    // Determine JP for this session
    $session_jp = 2; // Default
    if (isset($fourHourMapelIds[$j['mapel_id']])) {
        $session_jp = 4;
    }
    if (isset($teacherSummary[$gid])) {
        if ($j['nama_mapel']) $teacherSummary[$gid]['mapels'][$j['nama_mapel']] = true;
        $teacherSummary[$gid]['total_jp_calculated'] += $session_jp;
    }
}

// Filter Guru
foreach ($teacherSummary as $gid => $t) {
    $isSpecial = (stripos($t['bidang'], 'Kepala Madrasah') !== false || stripos($t['bidang'], 'Kurikulum') !== false || stripos($t['nama'], 'Lina Nurhasanah') !== false || stripos($t['nama'], 'Dedi Sobana') !== false);
    if ($t['total_jp'] == 0 && !$isSpecial) {
        unset($teacherSummary[$gid]); // Use total_jp_calculated for filtering
    }
}

$jadwalGrid = [];
foreach ($jadwals as $j) {
    $t_mulai = substr($j['jam_mulai'], 0, 5);
    $t_selesai = substr($j['jam_selesai'], 0, 5);
    $key = $j['kelas_id'] . '_' . $j['hari'] . '_' . $t_mulai . '_' . $t_selesai;
    if (!isset($jadwalGrid[$key])) {
        $jadwalGrid[$key] = [];
    }
    $jadwalGrid[$key][] = $j['guru_kode'];
}

// Kelompokkan jam pelajaran per hari
$jamPerHari = [];
foreach ($jamPelajaranRaw as $jam) {
    $jamPerHari[$jam['hari']][] = $jam;
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Export Jadwal — MTs Al-Ihsan</title>
    <style>
        @page { size: A4 landscape; margin: 0.5cm; }
        body { font-family: Arial, sans-serif; font-size: 8pt; margin: 0; padding: 0; }
        .page { width: 100%; padding: 5mm; background: #fff; }
        .header { text-align: center; border-bottom: 2px solid #000; margin-bottom: 5mm; padding-bottom: 3mm; }
        .header h1 { font-size: 14pt; margin: 0; }
        .title { text-align: center; font-weight: bold; font-size: 11pt; margin-bottom: 3mm; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 5mm; table-layout: fixed; }
        th, td { border: 1px solid #000; padding: 1mm; text-align: center; overflow: hidden; }
        th { background: #eee; font-size: 7.5pt; }
        .guru-kode { font-weight: bold; font-size: 9pt; }
        .day-label { writing-mode: vertical-rl; transform: rotate(180deg); background: #eee; font-weight: bold; width: 25px; }
        .no-print { background: #f0f0f0; padding: 10px; text-align: center; border-bottom: 1px solid #ccc; }
        .text-left { text-align: left; padding-left: 1mm; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">CETAK / SAVE PDF</button>
</div>

<!-- RINGKASAN BEBAN -->
<div class="page">
    <div class="header">
        <h1>DAFTAR BEBAN MENGAJAR GURU</h1>
        <p>Tahun Pelajaran <?php echo htmlspecialchars($tapel_name); ?> - Semester <?php echo $semester == 1 ? 'Ganjil' : 'Genap'; ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 30px;">NO</th>
                <th style="width: 50px;">KODE</th>
                <th>NAMA GURU</th>
                <th>BIDANG STUDI / JABATAN</th>
                <th style="width: 60px;">TOTAL JP</th>
            </tr>
        </thead>
        <tbody>
            <?php $no=1; foreach($teacherSummary as $t): 
                $jp = $t['total_jp_calculated'];
                if (stripos($t['bidang'], 'Kepala Madrasah') !== false || stripos($t['nama'], 'Lina Nurhasanah') !== false || stripos($t['bidang'], 'Kurikulum') !== false || stripos($t['nama'], 'Dedi Sobana') !== false) { // Override for special roles
                    $jp = 24;
                }
            ?>
            <tr>
                <td><?php echo $no++; ?></td>
                <td style="font-weight: bold;"><?php echo $t['kode']; ?></td>
                <td class="text-left"><?php echo $t['nama']; ?></td>
                <td class="text-left"><?php echo $t['bidang']; ?></td>
                <td style="font-weight: bold;"><?php echo $jp; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="page-break" style="page-break-after: always;"></div>

<div class="page">
    <div class="header">
        <h1>JADWAL PELAJARAN MTs AL-IHSAN BATUJAJAR</h1>
        <p>Tahun Pelajaran <?php echo htmlspecialchars($tapel_name); ?> - Semester <?php echo $semester == 1 ? 'Ganjil' : 'Genap'; ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 25px;">HARI</th>
                <th style="width: 60px;">WAKTU</th>
                <?php foreach ($kelas as $k): ?>
                    <th><?php echo $k['nama_kelas']; ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($jamPerHari as $hari => $sesiList): ?>
                <?php foreach ($sesiList as $idx => $jam): ?>
                    <tr>
                        <?php if ($idx === 0): ?>
                            <td class="day-label" rowspan="<?php echo count($sesiList); ?>">
                                <?php echo strtoupper($hari); ?>
                            </td>
                        <?php endif; ?>
                        <td>
                            Sesi <?php echo $jam['sesi_ke']; ?><br>
                            <?php echo substr($jam['jam_mulai'],0,5); ?>-<?php echo substr($jam['jam_selesai'],0,5); ?>
                        </td>
                        <?php foreach ($kelas as $k): ?>
                            <?php 
                            $t_mulai = substr($jam['jam_mulai'], 0, 5);
                            $t_selesai = substr($jam['jam_selesai'], 0, 5);
                            $key = $k['id'] . '_' . $hari . '_' . $t_mulai . '_' . $t_selesai;
                            $codes = $jadwalGrid[$key] ?? [];
                            ?>
                            <td>
                                <?php if (!empty($codes)): ?>
                                    <span class="guru-kode"><?php echo implode('/', $codes); ?></span>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="margin-top: 5mm;">
        <div style="font-weight: bold; margin-bottom: 1mm; font-size: 9pt;">Daftar Kode Guru:</div>
        <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 1mm; font-size: 7pt;">
            <?php foreach ($allGurus as $g): ?>
                <div style="border-bottom: 0.1px solid #eee;"><strong><?php echo $g['kode']; ?></strong>: <?php echo htmlspecialchars($g['nama']); ?></div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

</body>
</html>
