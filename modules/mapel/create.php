<?php
// emadrasah/modules/mapel/create.php
$page_title = 'Tambah Mata Pelajaran';
include __DIR__ . '/../../includes/header.php';
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title">Tambah Mata Pelajaran</h2>
        <p class="page-subtitle">Input mata pelajaran baru.</p>
    </div>
    <div>
        <a href="<?php echo base_url('modules/mapel/index.php'); ?>" class="btn btn-light border">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm p-4 fade-in" style="max-width: 600px;">
    <form action="<?php echo base_url('modules/mapel/store.php'); ?>" method="POST">
        <?php echo csrf_input(); ?>
        <div class="mb-3">
            <label class="form-label">Nama Mata Pelajaran <span class="text-danger">*</span></label>
            <input type="text" name="nama_mapel" class="form-control" required placeholder="Contoh: Bahasa Indonesia">
        </div>

        <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary px-5 fw-bold">Simpan Mata Pelajaran</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
