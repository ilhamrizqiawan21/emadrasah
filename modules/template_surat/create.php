<?php
// emadrasah/modules/template_surat/create.php
$page_title = 'Tambah Template Surat';
include __DIR__ . '/../../includes/header.php';
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title">Tambah Template Baru</h2>
        <p class="page-subtitle">Gunakan placeholder seperti {nomor_surat}, {tanggal}, {tujuan} dalam konten.</p>
    </div>
    <div>
        <a href="index.php" class="btn btn-light border">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm p-4 fade-in">
    <form action="store.php" method="POST">
        <?php echo csrf_input(); ?>
        <div class="mb-3">
            <label class="form-label small fw-bold">Nama Template <span class="text-danger">*</span></label>
            <input type="text" name="nama_template" class="form-control" placeholder="Contoh: Surat Keterangan Aktif" required>
        </div>
        <div class="mb-4">
            <label class="form-label small fw-bold">Konten Template (HTML) <span class="text-danger">*</span></label>
            <textarea name="konten" class="form-control" rows="15" placeholder="Tulis konten surat di sini..."></textarea>
            <div class="form-text mt-2">
                <strong>Tips:</strong> Anda bisa menggunakan tag HTML untuk formatting. Placeholder yang tersedia: 
                <code class="bg-light px-1">{nomor_surat}</code>, <code class="bg-light px-1">{tanggal}</code>, 
                <code class="bg-light px-1">{tujuan}</code>, <code class="bg-light px-1">{perihal}</code>.
            </div>
        </div>
        <div class="text-end">
            <button type="submit" class="btn btn-primary px-5 fw-bold">Simpan Template</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>