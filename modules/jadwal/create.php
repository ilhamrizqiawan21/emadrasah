<?php
// emadrasah/modules/jadwal/create.php
$page_title = 'Tambah Jadwal';
include __DIR__ . '/../../includes/header.php';

// Ambil data master
$kelas = $pdo->query("SELECT * FROM kelas ORDER BY nama_kelas ASC")->fetchAll();
$gurus = $pdo->query("SELECT * FROM gurus ORDER BY kode ASC")->fetchAll();
$mapels = $pdo->query("SELECT * FROM mapels ORDER BY nama_mapel ASC")->fetchAll();
$tahun_pelajaran = $pdo->query("SELECT * FROM tahun_pelajaran ORDER BY is_aktif DESC, kode DESC")->fetchAll();

// Ambil jam pelajaran (hari + sesi + waktu)
$jam_list = $pdo->query("SELECT * FROM jam_pelajaran ORDER BY FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'), sesi_ke ASC")->fetchAll();

$tp_aktif = $pdo->query("SELECT * FROM tahun_pelajaran WHERE is_aktif = 1 LIMIT 1")->fetch();
$semester_default = isset($_GET['semester']) ? (int)$_GET['semester'] : 1;
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title"><i class="fas fa-plus-circle me-2 text-primary"></i>Tambah Jadwal Pelajaran</h2>
        <p class="page-subtitle">Tambahkan satu entri jadwal pelajaran untuk kelas tertentu.</p>
    </div>
    <div>
        <a href="index.php" class="btn btn-light border">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm p-4 fade-in" style="max-width: 700px;">
    <form action="store.php" method="POST">
        <?php echo csrf_input(); ?>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label small fw-bold">Kelas <span class="text-danger">*</span></label>
                <select name="kelas_id" class="form-select" required>
                    <option value="">-- Pilih Kelas --</option>
                    <?php foreach ($kelas as $k): ?>
                        <option value="<?php echo $k['id']; ?>"><?php echo htmlspecialchars($k['nama_kelas']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Jam Pelajaran <span class="text-danger">*</span></label>
                <select name="jam_id" class="form-select" required>
                    <option value="">-- Pilih Sesi Jam --</option>
                    <?php foreach ($jam_list as $j): ?>
                        <option value="<?php echo $j['id']; ?>"
                            data-hari="<?php echo $j['hari']; ?>"
                            data-mulai="<?php echo substr($j['jam_mulai'], 0, 5); ?>"
                            data-selesai="<?php echo substr($j['jam_selesai'], 0, 5); ?>">
                            <?php echo $j['hari']; ?> &mdash; Sesi <?php echo $j['sesi_ke']; ?> (<?php echo substr($j['jam_mulai'], 0, 5); ?>–<?php echo substr($j['jam_selesai'], 0, 5); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label small fw-bold">Guru <span class="text-danger">*</span></label>
                <select name="guru_id" class="form-select" required>
                    <option value="">-- Pilih Guru --</option>
                    <?php foreach ($gurus as $g): ?>
                        <option value="<?php echo $g['id']; ?>">
                            [<?php echo htmlspecialchars($g['kode']); ?>] <?php echo htmlspecialchars($g['nama']); ?>
                            <?php echo $g['bidang_studi'] ? ' — ' . htmlspecialchars($g['bidang_studi']) : ''; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Mata Pelajaran <span class="text-danger">*</span></label>
                <select name="mapel_id" class="form-select" required>
                    <option value="">-- Pilih Mapel --</option>
                    <?php foreach ($mapels as $m): ?>
                        <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['nama_mapel']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label small fw-bold">Tahun Pelajaran <span class="text-danger">*</span></label>
                <select name="tahun_ajaran" class="form-select" required>
                    <option value="">-- Pilih Tahun --</option>
                    <?php foreach ($tahun_pelajaran as $tp): ?>
                        <option value="<?php echo htmlspecialchars($tp['kode']); ?>"
                            <?php echo ($tp['is_aktif']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($tp['kode']); ?>
                            <?php echo $tp['is_aktif'] ? ' (Aktif)' : ''; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Semester <span class="text-danger">*</span></label>
                <select name="semester" class="form-select" required>
                    <option value="1" <?php echo $semester_default == 1 ? 'selected' : ''; ?>>Ganjil (1)</option>
                    <option value="2" <?php echo $semester_default == 2 ? 'selected' : ''; ?>>Genap (2)</option>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold">Ruangan <span class="text-muted fw-normal">(opsional)</span></label>
            <input type="text" name="ruang" class="form-control" placeholder="Contoh: 7A, Lab IPA" maxlength="255">
        </div>

        <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary px-5 fw-bold">
                <i class="fas fa-save me-2"></i>Simpan Jadwal
            </button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
