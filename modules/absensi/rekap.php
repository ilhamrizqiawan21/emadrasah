<?php
// emadrasah/modules/absensi/rekap.php
$page_title = 'Rekap Absensi Bulanan';
include __DIR__ . '/../../includes/header.php';

$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : (int)date('m');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : (int)date('Y');

// Ambil Data Absensi Tergroup per Guru
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

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title"><i class="fas fa-chart-line me-2 text-primary"></i>Rekap Absensi Guru</h2>
        <p class="page-subtitle">Laporan kehadiran guru periode <strong><?php echo $nama_bulan[$bulan] . ' ' . $tahun; ?></strong></p>
    </div>
    <div class="d-flex gap-2">
        <a href="rekap_print.php?bulan=<?php echo $bulan; ?>&tahun=<?php echo $tahun; ?>" target="_blank" class="btn btn-outline-danger">
            <i class="fas fa-file-pdf me-1"></i> Cetak Laporan
        </a>
        <a href="index.php" class="btn btn-secondary">
            <i class="fas fa-calendar-day me-1"></i> Absensi Harian
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold">Bulan</label>
                <select name="bulan" class="form-select">
                    <?php for($i=1; $i<=12; $i++): ?>
                    <option value="<?php echo $i; ?>" <?php echo $i == $bulan ? 'selected' : ''; ?>><?php echo $nama_bulan[$i]; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Tahun</label>
                <select name="tahun" class="form-select">
                    <?php for($i=date('Y'); $i>=2024; $i--): ?>
                    <option value="<?php echo $i; ?>" <?php echo $i == $tahun ? 'selected' : ''; ?>><?php echo $i; ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3" style="width: 80px;">Kode</th>
                    <th class="py-3">Nama Guru</th>
                    <th class="py-3 text-center text-success">Hadir</th>
                    <th class="py-3 text-center text-warning">Izin</th>
                    <th class="py-3 text-center text-info">Sakit</th>
                    <th class="py-3 text-center text-danger">Alpha</th>
                    <th class="py-3 text-center fw-bold">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rekap_list as $r): ?>
                <?php $total = $r['hadir'] + $r['izin'] + $r['sakit'] + $r['alpha']; ?>
                <tr>
                    <td class="px-4 fw-bold"><?php echo $r['kode']; ?></td>
                    <td class="fw-bold"><?php echo $r['nama']; ?></td>
                    <td class="text-center"><?php echo $r['hadir']; ?></td>
                    <td class="text-center"><?php echo $r['izin']; ?></td>
                    <td class="text-center"><?php echo $r['sakit']; ?></td>
                    <td class="text-center"><?php echo $r['alpha']; ?></td>
                    <td class="text-center bg-light fw-bold"><?php echo $total; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
