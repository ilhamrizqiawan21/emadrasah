<?php
// emadrasah/modules/jadwal/index.php
$page_title = 'Jadwal Pelajaran';
include __DIR__ . '/../../includes/header.php';

$kelasCount = $pdo->query("SELECT COUNT(*) FROM kelas")->fetchColumn();
$jadwalCount = $pdo->query("SELECT COUNT(*) FROM jadwals")->fetchColumn();

// Dapatkan tahun pelajaran aktif dari tabel tahun_pelajaran
$tp_aktif = $pdo->query("SELECT * FROM tahun_pelajaran WHERE is_aktif = 1 LIMIT 1")->fetch();
$tahun_pelajaran = $pdo->query("SELECT * FROM tahun_pelajaran ORDER BY id DESC")->fetchAll();
$semester = isset($_GET['semester']) ? (int)$_GET['semester'] : 1;

// Gunakan tahun aktif sebagai default jika tidak ada parameter
$tahun_default = $tp_aktif ? $tp_aktif['kode'] : '';
$tahun_manual = isset($_GET['tahun_manual']) ? trim($_GET['tahun_manual']) : $tahun_default;

// Ambil daftar jadwal untuk ditampilkan
$stmtJadwal = $pdo->prepare("
    SELECT j.*, k.nama_kelas, g.kode as guru_kode, g.nama as guru_nama, m.nama_mapel
    FROM jadwals j
    JOIN kelas k ON j.kelas_id = k.id
    JOIN gurus g ON j.guru_id = g.id
    JOIN mapels m ON j.mapel_id = m.id
    WHERE j.semester = ? AND j.tahun_ajaran = ?
    ORDER BY FIELD(j.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'), j.jam_mulai ASC
");
$stmtJadwal->execute([$semester, $tahun_manual]);
$jadwal_list = $stmtJadwal->fetchAll();
?>

<?php display_flash(); ?>
<div class="page-header fade-in">
    <div>
        <h2 class="page-title"><i class="fas fa-calendar-check me-2 text-primary"></i>Jadwal Pelajaran</h2>
        <p class="page-subtitle">Kelola jadwal pelajaran berbasis hari, kelas, guru, dan sesi.</p>
    </div>
</div>

<!-- Dashboard Control Center -->
<div class="row g-3 mb-4 fade-in">
    <div class="col-12">
        <div class="card border-0 shadow-sm p-4">
            <h5 class="mb-4"><i class="fas fa-sliders me-2 text-primary"></i>Panel Kontrol Jadwal</h5>
            
            <form method="GET" id="mainFilterForm">
            <!-- Baris 1: Filter kontrol -->
            <div class="row g-3 mb-3">
                <div class="col-md-4">
                    <label for="tahun_manual" class="form-label fw-bold mb-2">Tahun Pelajaran</label>
                    <select name="tahun_manual" id="tahun_manual" class="form-select" required>
                        <option value="">-- Pilih Tahun Pelajaran --</option>
                        <?php foreach ($tahun_pelajaran as $tp): ?>
                            <option value="<?php echo htmlspecialchars($tp['kode']); ?>" 
                                <?php echo ($tp['kode'] === $tahun_manual || $tp['is_aktif']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($tp['kode']); ?>
                                <?php echo $tp['is_aktif'] ? ' (Aktif)' : ''; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="semester" class="form-label fw-bold mb-2">Semester</label>
                    <select name="semester" id="semester" class="form-select">
                        <option value="1" <?php echo ($semester == 1) ? 'selected' : ''; ?>>Ganjil (1)</option>
                        <option value="2" <?php echo ($semester == 2) ? 'selected' : ''; ?>>Genap (2)</option>
                    </select>
                </div>
            </div>

            <!-- Baris 2: Info tahun aktif + tombol aksi -->
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <div class="d-flex align-items-center gap-2">
                        <?php if ($tp_aktif): ?>
                            <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Tahun Aktif: <?php echo htmlspecialchars($tp_aktif['kode']); ?></span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark"><i class="fas fa-exclamation-triangle me-1"></i>Belum ada tahun pelajaran aktif</span>
                        <?php endif; ?>
                        <a href="<?php echo base_url('modules/tahun_pelajaran/index.php'); ?>" class="small ms-1" target="_blank">
                            <i class="fas fa-external-link-alt"></i> Kelola
                        </a>
                    </div>
                </div>
                <div class="col-md-2">
                    <a href="create.php" class="btn btn-success w-100 py-2">
                        <i class="fas fa-plus me-2"></i>Tambah Jadwal
                    </a>
                </div>
                <div class="col-md-2">
                    <button type="button" onclick="goToGrid()" class="btn btn-primary w-100 py-2">
                        <i class="fas fa-table-cells me-2"></i>Buka Grid
                    </button>
                </div>
                <div class="col-md-3">
                    <button type="button" onclick="goToPrint()" class="btn btn-outline-success w-100 py-2">
                        <i class="fas fa-print me-2"></i>Cetak Jadwal
                    </button>
                </div>
            </div>
            </form>
        </div>
    </div>
</div>

<div class="row g-3 mb-4 fade-in">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm p-4 h-100">
            <h5 class="mb-3">Ringkasan Data</h5>
            <p class="mb-1"><strong>Kelas tersedia:</strong> <?php echo $kelasCount; ?></p>
            <p class="mb-0"><strong>Entri jadwal:</strong> <?php echo $jadwalCount; ?></p>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm p-4 h-100">
            <h5 class="mb-3">Petunjuk Singkat</h5>
            <ul class="small mb-0">
                <li>Gunakan <strong>Tambah Jadwal</strong> untuk entri satuan.</li>
                <li>Gunakan <strong>Buka Grid</strong> untuk input massal.</li>
                <li>Gunakan <strong>Cetak Jadwal</strong> untuk mencetak PDF.</li>
            </ul>
        </div>
    </div>
</div>

<!-- Daftar Jadwal Tersimpan -->
<div class="card border-0 shadow-sm overflow-hidden fade-in">
    <div class="card-header bg-white border-bottom py-3">
        <h5 class="mb-0"><i class="fas fa-list me-2 text-primary"></i>Daftar Jadwal — <?php echo htmlspecialchars($tahun_manual); ?> Semester <?php echo $semester; ?></h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3">Kelas</th>
                    <th class="py-3">Hari</th>
                    <th class="py-3 text-center">Jam</th>
                    <th class="py-3">Guru</th>
                    <th class="py-3">Mapel</th>
                    <th class="py-3">Ruangan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($jadwal_list)): ?>
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="fas fa-calendar-alt fa-3x mb-3 d-block opacity-25"></i>
                        Belum ada jadwal untuk filter ini.
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($jadwal_list as $j): ?>
                    <tr>
                        <td class="px-4 fw-bold"><?php echo htmlspecialchars($j['nama_kelas']); ?></td>
                        <td><?php echo $j['hari']; ?></td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border">
                                <?php echo substr($j['jam_mulai'], 0, 5); ?> – <?php echo substr($j['jam_selesai'], 0, 5); ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-secondary me-1"><?php echo htmlspecialchars($j['guru_kode']); ?></span>
                            <?php echo htmlspecialchars($j['guru_nama']); ?>
                        </td>
                        <td><?php echo htmlspecialchars($j['nama_mapel']); ?></td>
                        <td><?php echo $j['ruang'] ? htmlspecialchars($j['ruang']) : '<span class="text-muted">—</span>'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function goToGrid() {
    const tahun = document.getElementById('tahun_manual').value;
    const semester = document.getElementById('semester').value;
    if (!tahun) { alert('Tahun Pelajaran harus diisi!'); return; }
    
    window.location.href = `<?php echo base_url('modules/jadwal/grid.php'); ?>?semester=${semester}&tahun_manual=${encodeURIComponent(tahun)}`;
}

function goToPrint() {
    const tahun = document.getElementById('tahun_manual').value;
    const semester = document.getElementById('semester').value;
    if (!tahun) { alert('Tahun Pelajaran harus diisi!'); return; }
    
    window.location.href = `<?php echo base_url('modules/jadwal/cetak_jadwal.php'); ?>?semester=${semester}&tahun_manual=${encodeURIComponent(tahun)}`;
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
