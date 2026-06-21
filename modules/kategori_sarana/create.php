<?php
// emadrasah/modules/kategori_sarana/create.php
$page_title = 'Tambah Kategori Sarana';
include __DIR__ . '/../../includes/header.php';
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title">Tambah Kategori</h2>
        <p class="page-subtitle">Buat kategori baru untuk aset sarana prasarana sekolah.</p>
    </div>
    <div>
        <a href="index.php" class="btn btn-light border">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm p-4 fade-in" style="max-width: 600px;">
    <form action="store.php" method="POST">
        <?php echo csrf_input(); ?>
        <div class="mb-3">
            <label class="form-label small fw-bold">Nama Kategori <span class="text-danger">*</span></label>
            <input type="text" name="nama_kategori" class="form-control" required placeholder="Contoh: Elektronik">
        </div>
        <div class="mb-3">
            <label class="form-label small fw-bold">Keterangan / Deskripsi</label>
            <textarea name="deskripsi" class="form-control" rows="3" placeholder="Jelaskan kategori ini..."></textarea>
        </div>

        <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary px-5 fw-bold">Simpan Kategori</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
