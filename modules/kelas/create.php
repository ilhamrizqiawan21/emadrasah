<?php
// emadrasah/modules/kelas/create.php
$page_title = 'Tambah Kelas';
include __DIR__ . '/../../includes/header.php';

// Ambil data guru untuk Wali Kelas
$guru_list = $pdo->query("SELECT id, nama FROM gurus ORDER BY nama ASC")->fetchAll();
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title">Tambah Kelas</h2>
        <p class="page-subtitle">Input data kelas baru dan tentukan wali kelas.</p>
    </div>
    <div>
        <a href="<?php echo base_url('modules/kelas/index.php'); ?>" class="btn btn-light border">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm p-4 fade-in">
    <form action="store.php" method="POST">
        <?php echo csrf_input(); ?>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Tingkat <span class="text-danger">*</span></label>
                <select name="tingkat" class="form-select" required>
                    <option value="7">7</option>
                    <option value="8">8</option>
                    <option value="9">9</option>
                </select>
            </div>
            <div class="col-md-8">
                <label class="form-label">Nama Kelas <span class="text-danger">*</span></label>
                <input type="text" name="nama_kelas" class="form-control" required placeholder="Contoh: Kelas 7A">
            </div>
            <div class="col-md-6">
                <label class="form-label">Wali Kelas</label>
                <select name="guru_pembimbing_id" class="form-select">
                    <option value="">-- Pilih Wali Kelas --</option>
                    <?php foreach($guru_list as $g): ?>
                    <option value="<?php echo $g['id']; ?>"><?php echo $g['nama']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Kapasitas</label>
                <input type="number" name="kapasitas" class="form-control" placeholder="40">
            </div>
            <div class="col-md-3">
                <label class="form-label">Ruangan</label>
                <input type="text" name="ruangan" class="form-control" placeholder="Lantai 1">
            </div>
        </div>

        <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary px-5 fw-bold">Simpan Data Kelas</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
