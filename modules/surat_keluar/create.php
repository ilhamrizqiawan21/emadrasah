<?php
// emadrasah/modules/surat_keluar/create.php
$page_title = 'Registrasi Surat Keluar';
include __DIR__ . '/../../includes/header.php';

// Generate Nomor Surat Otomatis (SK-YYYY-XXX)
$year = date('Y');
$stmt = $pdo->prepare("SELECT nomor_surat FROM surat_keluar WHERE nomor_surat LIKE ? ORDER BY id DESC LIMIT 1");
$stmt->execute(["SK-$year-%"]);
$last = $stmt->fetchColumn();

if ($last) {
    $num = (int)substr($last, -3) + 1;
} else {
    $num = 1;
}
$auto_nomor = "SK-$year-" . str_pad($num, 3, '0', STR_PAD_LEFT);
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title">Registrasi Surat Keluar</h2>
        <p class="page-subtitle">Masukkan detail surat yang dikirim oleh Madrasah.</p>
    </div>
    <div>
        <a href="index.php" class="btn btn-light border">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm p-4 fade-in">
    <form action="store.php" method="POST" enctype="multipart/form-data">
        <?php echo csrf_input(); ?>
        <div class="row g-3">
            <div class="col-md-12 mb-2">
                <label class="form-label small fw-bold text-primary">Gunakan Template (Opsional)</label>
                <?php $templates = $pdo->query("SELECT id, nama_template FROM template_surat ORDER BY nama_template ASC")->fetchAll(); ?>
                <select name="template_id" class="form-select border-primary-subtle bg-light-subtle">
                    <option value="">-- Pilih Template Jika Ingin Cetak Otomatis --</option>
                    <?php foreach($templates as $t): ?>
                    <option value="<?php echo $t['id']; ?>"><?php echo $t['nama_template']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Nomor Surat <span class="text-danger">*</span></label>
                <input type="text" name="nomor_surat" class="form-control" required value="<?php echo $auto_nomor; ?>">
            </div>
            <div class="col-md-8">
                <label class="form-label small fw-bold">Tujuan Surat <span class="text-danger">*</span></label>
                <input type="text" name="tujuan" class="form-control" required placeholder="Instansi/Tujuan">
            </div>
            <div class="col-md-12">
                <label class="form-label small fw-bold">Perihal <span class="text-danger">*</span></label>
                <input type="text" name="perihal" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Tanggal Kirim <span class="text-danger">*</span></label>
                <input type="date" name="tanggal_kirim" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="col-md-8">
                <label class="form-label small fw-bold">Lampiran</label>
                <input type="text" name="lampiran" class="form-control" placeholder="Contoh: 1 Berkas">
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Upload Draft/Scan Surat (PDF/Gambar)</label>
                <input type="file" name="file_draft" class="form-control" accept=".pdf,image/*">
            </div>
        </div>

        <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary px-5 fw-bold">Simpan Surat Keluar</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>