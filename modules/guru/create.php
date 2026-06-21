<?php
$page_title = 'Tambah Guru';
include __DIR__ . '/../../includes/header.php';

// Ambil daftar mata pelajaran dari tabel mapels
$stmtMapel = $pdo->query("SELECT id, nama_mapel FROM mapels ORDER BY nama_mapel ASC");
$mapels = $stmtMapel->fetchAll();
?>

<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title">Tambah Guru</h2>
        <p class="page-subtitle">Input data tenaga pendidik baru.</p>
    </div>
    <div>
        <a href="<?php echo base_url('guru'); ?>" class="btn btn-light border">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm p-4 fade-in">
    <form action="<?php echo base_url('guru/store.php'); ?>" method="POST">
        <?php echo csrf_input(); ?>
        <div class="row g-3">
            <div class="col-md-4">
                <label for="kode" class="form-label">Kode Guru <span class="text-danger">*</span></label>
                <input type="text" id="kode" name="kode" class="form-control" required placeholder="Contoh: G01">
            </div>
            <div class="col-md-8">
                <label for="nama" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" id="nama" name="nama" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label for="nip" class="form-label">NIP</label>
                <input type="text" id="nip" name="nip" class="form-control">
            </div>
            <div class="col-md-6">
                <label for="bidang_studi" class="form-label">Bidang Studi / Mata Pelajaran</label>
                <select id="bidang_studi" name="bidang_studi" class="form-select">
                    <option value="">-- Pilih Mata Pelajaran --</option>
                    <?php foreach ($mapels as $m): ?>
                        <option value="<?= htmlspecialchars($m['nama_mapel']) ?>">
                            <?= htmlspecialchars($m['nama_mapel']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <small class="text-muted">Pilih mata pelajaran yang diampu oleh guru ini.</small>
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-control">
            </div>
            <div class="col-md-6">
                <label for="phone" class="form-label">No. Telepon</label>
                <input type="text" id="phone" name="phone" class="form-control">
            </div>
            <div class="col-md-4">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="aktif">Aktif</option>
                    <option value="cuti">Cuti</option>
                    <option value="pensiun">Pensiun</option>
                </select>
            </div>
        </div>

        <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary px-5 fw-bold">Simpan Data Guru</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>