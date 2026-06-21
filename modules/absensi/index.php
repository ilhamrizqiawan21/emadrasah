<?php
// emadrasah/modules/absensi/index.php
$page_title = 'Absensi Guru Harian';
include __DIR__ . '/../../includes/header.php';

$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');

// Ambil Data Guru
$stmt_guru = $pdo->query("SELECT * FROM gurus ORDER BY nama ASC");
$gurus = $stmt_guru->fetchAll();

// Ambil Data Absensi (Agenda) untuk tanggal terpilih
$stmt_agenda = $pdo->prepare("SELECT * FROM agenda_guru WHERE tanggal = ?");
$stmt_agenda->execute([$tanggal]);
$agendas_raw = $stmt_agenda->fetchAll();
$agendas = [];
foreach ($agendas_raw as $a) {
    $agendas[$a['guru_id']] = $a;
}
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title"><i class="fas fa-fingerprint me-2 text-primary"></i>Absensi Guru Harian</h2>
        <p class="page-subtitle">Pencatatan kehadiran guru untuk tanggal yang dipilih.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="rekap.php" class="btn btn-outline-info">
            <i class="fas fa-chart-line me-1"></i> Rekap Bulanan
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-bold small text-muted">Pilih Tanggal</label>
                <div class="input-group">
                    <input type="date" name="tanggal" class="form-control" value="<?php echo $tanggal; ?>">
                    <button type="submit" class="btn btn-primary px-4">Tampilkan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<form action="store.php" method="POST">
        <?php echo csrf_input(); ?>
    <input type="hidden" name="tanggal" value="<?php echo $tanggal; ?>">
    <div class="card border-0 shadow-sm overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-center" style="width: 50px;">No</th>
                        <th class="py-3" style="width: 100px;">Kode</th>
                        <th class="py-3">Nama Guru</th>
                        <th class="py-3 text-center" style="width: 160px;">Status Kehadiran</th>
                        <th class="py-3 px-4">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($gurus)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">Data guru belum tersedia.</td>
                    </tr>
                    <?php else: ?>
                        <?php $no=1; foreach ($gurus as $g): ?>
                        <?php 
                            $status = $agendas[$g['id']]['status'] ?? 'hadir';
                            $ket = $agendas[$g['id']]['keterangan'] ?? '';
                        ?>
                        <tr>
                            <td class="text-center px-4"><?php echo $no++; ?></td>
                            <td><span class="badge bg-light text-primary border"><?php echo $g['kode']; ?></span></td>
                            <td class="fw-bold"><?php echo $g['nama']; ?></td>
                            <td>
                                <select name="status[<?php echo $g['id']; ?>]" class="form-select form-select-sm">
                                    <option value="hadir" <?php echo $status == 'hadir' ? 'selected' : ''; ?>>✅ Hadir</option>
                                    <option value="izin" <?php echo $status == 'izin' ? 'selected' : ''; ?>>📝 Izin</option>
                                    <option value="sakit" <?php echo $status == 'sakit' ? 'selected' : ''; ?>>🤒 Sakit</option>
                                    <option value="alpha" <?php echo $status == 'alpha' ? 'selected' : ''; ?>>❌ Alpha</option>
                                </select>
                            </td>
                            <td class="px-4">
                                <div class="d-flex align-items-center gap-2">
                                    <input type="text" name="keterangan[<?php echo $g['id']; ?>]" 
                                           class="form-control form-control-sm" 
                                           placeholder="Catatan..." 
                                           value="<?php echo $ket; ?>">
                                    
                                    <?php if (isset($agendas[$g['id']]) && $status != 'hadir'): ?>
                                        <a href="pengganti.php?agenda_id=<?php echo $agendas[$g['id']]['id']; ?>" 
                                           class="btn btn-xs btn-outline-info text-nowrap" style="font-size: 0.7rem; padding: 2px 8px;" 
                                           title="Tunjuk Pengganti">
                                            <i class="fas fa-user-friends"></i> Infaler
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white text-end py-3">
            <button type="submit" class="btn btn-primary px-5 fw-bold shadow-sm">
                <i class="fas fa-save me-2"></i>Simpan Seluruh Absensi
            </button>
        </div>
    </div>
</form>

<?php include __DIR__ . '/../../includes/footer.php'; ?>