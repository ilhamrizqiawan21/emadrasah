<?php
// emadrasah/modules/absensi/pengganti.php
$page_title = 'Tunjuk Guru Pengganti';
include __DIR__ . '/../../includes/header.php';

$agenda_id = isset($_GET['agenda_id']) ? (int)$_GET['agenda_id'] : 0;

// Ambil info agenda & guru utama
$stmt = $pdo->prepare("
    SELECT a.*, g.nama as nama_guru, g.kode as kode_guru 
    FROM agenda_guru a 
    JOIN gurus g ON a.guru_id = g.id 
    WHERE a.id = ?
");
$stmt->execute([$agenda_id]);
$agenda = $stmt->fetch();

if (!$agenda) {
    header('Location: index.php');
    exit;
}

// Tentukan Hari berdasarkan tanggal agenda
$hariIndoMap = [
    'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu', 
    'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
];
$nama_hari = $hariIndoMap[date('l', strtotime($agenda['tanggal']))];

// Ambil Jam Pelajaran di hari tersebut
$stmt_jam = $pdo->prepare("SELECT * FROM jam_pelajaran WHERE hari = ? ORDER BY sesi_ke ASC");
$stmt_jam->execute([$nama_hari]);
$jam_list = $stmt_jam->fetchAll();

// Ambil Daftar Guru Pengganti (selain guru utama)
$stmt_pilih = $pdo->prepare("SELECT id, nama, kode FROM gurus WHERE id != ? ORDER BY nama ASC");
$stmt_pilih->execute([$agenda['guru_id']]);
$guru_pilihan = $stmt_pilih->fetchAll();

// Ambil data pengganti yang sudah ada untuk agenda ini
$stmt_ex = $pdo->prepare("
    SELECT gp.*, g.nama as nama_pengganti 
    FROM guru_pengganti gp 
    JOIN gurus g ON gp.guru_pengganti_id = g.id 
    WHERE gp.agenda_guru_id = ?
");
$stmt_ex->execute([$agenda_id]);
$existing_pengganti = $stmt_ex->fetchAll();
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title"><i class="fas fa-user-friends me-2 text-info"></i>Manajemen Guru Pengganti</h2>
        <p class="page-subtitle">Guru Utama: <strong><?php echo $agenda['nama_guru']; ?></strong> (<?php echo $agenda['status']; ?>)</p>
    </div>
    <div>
        <a href="index.php?tanggal=<?php echo $agenda['tanggal']; ?>" class="btn btn-light border">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<div class="row g-4 fade-in">
    <div class="col-xl-4">
        <div class="card border-0 shadow-sm p-4 h-100">
            <h5 class="fw-bold mb-4">Tugaskan Infaler</h5>
            <form action="store_pengganti.php" method="POST">
        <?php echo csrf_input(); ?>
                <input type="hidden" name="agenda_guru_id" value="<?php echo $agenda_id; ?>">
                
                <div class="mb-3">
                    <label class="form-label small fw-bold">Jam Pelajaran</label>
                    <select name="jam_pelajaran_id" class="form-select" required>
                        <option value="">-- Pilih Sesi --</option>
                        <?php foreach($jam_list as $j): ?>
                        <option value="<?php echo $j['id']; ?>">Jam <?php echo $j['sesi_ke']; ?> (<?php echo substr($j['jam_mulai'],0,5); ?>-<?php echo substr($j['jam_selesai'],0,5); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Pilih Guru Infaler</label>
                    <select name="guru_pengganti_id" class="form-select" required>
                        <option value="">-- Pilih Guru --</option>
                        <?php foreach($guru_pilihan as $gp): ?>
                        <option value="<?php echo $gp['id']; ?>">[<?php echo $gp['kode']; ?>] <?php echo $gp['nama']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold">Keterangan Tugas</label>
                    <textarea name="keterangan" class="form-control" rows="3" placeholder="Contoh: Mengisi materi bab 2"></textarea>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-info text-white fw-bold">
                        <i class="fas fa-plus me-2"></i>Tugaskan Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="card border-0 shadow-sm overflow-hidden h-100">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">Daftar Infaler Hari Ini (<?php echo tgl_indo($agenda['tanggal']); ?>)</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3">Jam</th>
                            <th class="py-3">Guru Infaler</th>
                            <th class="py-3">Keterangan</th>
                            <th class="px-4 py-3 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($existing_pengganti)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">Belum ada guru pengganti yang ditugaskan.</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach($existing_pengganti as $ex): ?>
                            <tr>
                                <td class="px-4">
                                    <span class="badge bg-light text-dark border">Sesi <?php echo $ex['jam_pelajaran_id']; // Sederhanakan ID ke Sesi ?></span>
                                </td>
                                <td class="fw-bold"><?php echo $ex['nama_pengganti']; ?></td>
                                <td class="small text-muted"><?php echo $ex['keterangan'] ?: '-'; ?></td>
                                <td class="px-4 text-end">
                                    <form action="delete_pengganti.php" method="POST" class="d-inline" id="deleteForm-<?= $ex['id'] ?>">
                                    <?= csrf_input() ?>
                                    <input type="hidden" name="id" value="<?= $ex['id'] ?>">
                                    <input type="hidden" name="agenda_id" value="<?= $agenda_id ?>">
                                    <button type="button" class="btn btn-sm btn-light border" 
                                        onclick="showConfirmModal('Batalkan penugasan ini?', function() { document.getElementById('deleteForm-<?= $ex['id'] ?>').submit(); })" 
                                        title="Hapus">
                                        <i class="fas fa-times text-danger"></i>
                                    </button>
                                </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
