<?php
// emadrasah/modules/jadwal/cetak_jadwal.php
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

// Inisialisasi Ringkasan Guru dengan semua guru aktif agar urutan konsisten
$teacherSummary = [];
foreach ($allGurus as $g) {
    $teacherSummary[$g['id']] = [
        'nama' => $g['nama'],
        'kode' => $g['kode'],
        'bidang' => $g['bidang_studi'],
        'mapels' => [],
        'kelas' => [], // Keep for per-class count display
        'total_jp_calculated' => 0 // New field for calculated JP
    ];
}

// Isi data dari jadwal yang ada
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
        if ($j['nama_kelas']) $teacherSummary[$gid]['kelas'][$j['nama_kelas']] = true;
        $teacherSummary[$gid]['total_jp_calculated'] += $session_jp;
    }
}

// Filter tampilan: Tampilkan guru yang punya jam mengajar ATAU Kepala/Wakasek
foreach ($teacherSummary as $gid => $t) {
    $isSpecial = (stripos($t['bidang'], 'Kepala Madrasah') !== false || stripos($t['bidang'], 'Kurikulum') !== false || stripos($t['nama'], 'Lina Nurhasanah') !== false || stripos($t['nama'], 'Dedi Sobana') !== false);

    if ($t['total_jp_calculated'] == 0 && !$isSpecial) {
        unset($teacherSummary[$gid]);
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
    <title>Jadwal Pelajaran — MTs Al-Ihsan</title>
    <link rel="icon" type="image/png" href="<?php echo base_url('assets/images/logo.png'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        @page { size: A4 portrait; margin: 0.5cm; }
        .page-break { page-break-after: always; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 8.5pt; color: #333; margin: 0; padding: 0; background: #fff; line-height: 1.2; }
        .no-print { background: #f8fafc; padding: 15px; text-align: center; border-bottom: 2px solid #e2e8f0; margin-bottom: 20px; }
        .btn-print { padding: 10px 25px; background: #2563eb; color: #fff; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; font-size: 11pt; transition: all 0.2s; }
        .btn-print:hover { background: #1d4ed8; }
        .page { width: 210mm; min-height: 297mm; margin: 5mm auto; padding: 10mm; background: white; box-shadow: 0 0 10px rgba(0,0,0,0.1); position: relative; }
        .header { text-align: center; margin-bottom: 5mm; padding-bottom: 3mm; border-bottom: 2px solid #000; }
        .header-content { display: flex; align-items: center; justify-content: center; gap: 5mm; }
        .logo img { height: 18mm; }
        .header-text h1 { font-size: 12pt; font-weight: bold; margin: 0; color: #000; }
        .header-text h2 { font-size: 10pt; font-weight: bold; margin: 1px 0; }
        .header-text p { font-size: 7pt; margin: 1px 0; }
        .title { text-align: center; font-size: 10pt; font-weight: bold; margin: 3mm 0 1mm 0; text-transform: uppercase; }
        .subtitle { text-align: center; font-size: 9pt; font-weight: bold; margin: 0 0 4mm 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 5mm; }
        th, td { border: 1px solid #000; padding: 1mm 0.5mm; text-align: center; vertical-align: middle; }
        th { background-color: #f2f2f2; font-weight: bold; font-size: 7.5pt; }
        td { font-size: 7.5pt; }
        .text-left { text-align: left; padding-left: 2mm; }
        .footer-section { display: flex; justify-content: space-between; margin-top: 5mm; padding: 0 10mm; }
        .footer-item { text-align: center; width: 45%; }
        .signature-space { height: 15mm; }
        .signature-name { font-weight: bold; text-decoration: underline; }
        .day-label { font-weight: bold; background-color: #f2f2f2; width: 30px; writing-mode: vertical-rl; transform: rotate(180deg); }
        .time-col { width: 70px; font-weight: bold; }
        .guru-kode { font-weight: bold; font-size: 9pt; }
        .empty-cell { color: #ccc; }
        .legend-container { margin-top: 5mm; border-top: 1px dashed #ccc; padding-top: 3mm; }
        .legend-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 2mm; font-size: 7pt; }
        .legend-item { display: flex; align-items: center; gap: 1mm; }
        .legend-code { border: 1px solid #000; min-width: 5mm; height: 5mm; display: flex; align-items: center; justify-content: center; font-weight: bold; background: #eee; }
        @media print { .no-print { display: none; } .page { margin: 0; box-shadow: none; padding: 5mm; width: 100%; } .page-break { page-break-after: always; } body { background: none; } }
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()" class="btn-print">
        <i class="fas fa-print me-2"></i> CETAK JADWAL PELAJARAN
    </button>
    <a href="<?php echo base_url('modules/jadwal/grid.php?semester='.$semester.'&tahun_manual='.urlencode($tahun_manual)); ?>" style="margin-left: 10px; text-decoration: none; color: #666;">Kembali ke Grid</a>
</div>

<!-- HALAMAN 1: BEBAN MENGAJAR -->
<div class="page">
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <img src="<?php echo base_url('assets/images/logo.png'); ?>" alt="Logo">
            </div>
            <div class="header-text">
                <h1>YAYASAN AL-IHSAN GALANGGANG BATUJAJAR</h1>
                <h2>MADRASAH TSANAWIYAH "AL-IHSAN" BATUJAJAR</h2>
                <p>Notaris Delina Kania, S.H., S.P1., M.Pd Nomor AHU-0041601.AH.01.04 Tahun 2016</p>
                <p>Jl. Galanggang No. 69 RT 01 RW 18 Desa Galanggang Kec. Batujajar</p>
            </div>
        </div>
    </div>

    <div class="title">DAFTAR BEBAN MENGAJAR GURU</div>
    <div class="subtitle">Semester <?php echo $semester == 1 ? 'Ganjil' : 'Genap'; ?> Tahun Pelajaran <?php echo htmlspecialchars($tapel_name); ?></div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 25px;">NO</th>
                <th rowspan="2">NAMA GURU</th>
                <th rowspan="2" style="width: 35px;">KODE</th>
                <th rowspan="2">BIDANG STUDI</th>
                <th colspan="<?php echo count($kelas); ?>">KELAS</th>
                <th rowspan="2" style="width: 40px;">TOTAL</th>
            </tr>
            <tr>
                <?php foreach ($kelas as $k): ?>
                    <th style="font-size: 6.5pt; width: 15px;"><?php echo htmlspecialchars(substr($k['nama_kelas'], -2)); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; foreach ($teacherSummary as $gid => $t): ?>
                <tr>
                    <td><?php echo $no++; ?></td>
                    <td class="text-left"><?php echo htmlspecialchars($t['nama']); ?></td>
                    <td style="font-weight: bold;"><?php echo htmlspecialchars($t['kode']); ?></td>
                    <td class="text-left"><?php echo htmlspecialchars($t['bidang']); ?></td>
                    <?php 
                    $total_sesi = 0;
                    foreach ($kelas as $k): 
                        $count = 0;
                        foreach ($jadwals as $j) {
                            if ($j['guru_id'] == $gid && $j['kelas_id'] == $k['id']) $count++;
                        }
                        $total_sesi += $count;
                    ?>
                        <td><?php echo $count > 0 ? $count : '-'; ?></td>
                    <?php endforeach; ?>
                    <?php 
                        $total_jp_display = $t['total_jp_calculated'];
                        if (stripos($t['bidang'], 'Kepala Madrasah') !== false || stripos($t['nama'], 'Lina Nurhasanah') !== false || stripos($t['bidang'], 'Kurikulum') !== false || stripos($t['nama'], 'Dedi Sobana') !== false) { // Override for special roles
                            $total_jp_display = 24;
                        }
                    ?>
                    <td style="font-weight: bold;"><?php echo $total_jp_display; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer-section">
        <div class="footer-item">
            <p></p>
            <p></p>
            <div class="signature-space"></div>
            <p class="signature-name"></p>
            <p></p>
        </div>
        <div class="footer-item">
            <p>Batujajar, <?php echo date('d F Y'); ?></p>
            <p>Kepala MTs. Al-Ihsan Batujajar</p>
            <div class="signature-space"></div>
            <p class="signature-name">Dra. Hj. LINA NURHASANAH</p>
            <p>NIP. 196808111994032001</p>
        </div>
    </div>
</div>

<div class="page-break"></div>

<!-- HALAMAN 2: JADWAL GRID -->
<div class="page">
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <img src="<?php echo base_url('assets/images/logo.png'); ?>" alt="Logo">
            </div>
            <div class="header-text">
                <h1>JADWAL PELAJARAN MTs AL-IHSAN BATUJAJAR</h1>
                <h2>Semester <?php echo $semester == 1 ? 'Ganjil' : 'Genap'; ?> Tahun Pelajaran <?php echo htmlspecialchars($tapel_name); ?></h2>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 30px;">HARI</th>
                <th style="width: 80px;">WAKTU</th>
                <?php foreach ($kelas as $k): ?>
                    <th style="font-size: 7pt;"><?php echo $k['nama_kelas']; ?></th>
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
                        <td class="time-col">
                            Sesi <?php echo $jam['sesi_ke']; ?><br>
                            <span style="font-size: 6.5pt; font-weight: normal;">
                                <?php echo substr($jam['jam_mulai'],0,5); ?>-<?php echo substr($jam['jam_selesai'],0,5); ?>
                            </span>
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
                                    <span class="empty-cell">—</span>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="legend-container">
        <div style="font-weight: bold; margin-bottom: 2mm;">DAFTAR KODE GURU:</div>
        <div class="legend-grid">
            <?php foreach ($allGurus as $g): ?>
                <div class="legend-item">
                    <div class="legend-code"><?php echo $g['kode']; ?></div>
                    <span><?php echo htmlspecialchars($g['nama']); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

</body>
</html>
