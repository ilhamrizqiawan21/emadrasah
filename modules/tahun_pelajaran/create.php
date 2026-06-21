<?php
// emadrasah/modules/tahun_pelajaran/create.php
$page_title = 'Tambah Tahun Pelajaran';
include __DIR__ . '/../../includes/header.php';
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title">Tambah Tahun Pelajaran</h2>
        <p class="page-subtitle">Buat periode tahun ajaran baru.</p>
    </div>
    <div>
        <a href="<?php echo base_url('modules/tahun_pelajaran/index.php'); ?>" class="btn btn-light border">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm p-4 fade-in" style="max-width: 600px;">
    <form action="<?php echo base_url('modules/tahun_pelajaran/store.php'); ?>" method="POST">
        <?php echo csrf_input(); ?>
        <div class="mb-3">
            <label class="form-label">Kode <span class="text-danger">*</span></label>
            <input type="text" name="kode" class="form-control" required placeholder="Contoh: 2024/2025">
            <small class="text-muted">Gunakan format YYYY/YYYY.</small>
        </div>
        <div class="mb-3">
            <label class="form-label">Nama Tahun Pelajaran <span class="text-danger">*</span></label>
            <input type="text" name="nama" class="form-control" required placeholder="Contoh: Tahun Pelajaran 2024/2025">
        </div>
        <div class="mb-3">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="is_aktif" id="isAktif" value="1">
                <label class="form-check-label" for="isAktif">Set sebagai Tahun Pelajaran Aktif</label>
            </div>
        </div>

        <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary px-5 fw-bold">Simpan Tahun Pelajaran</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>