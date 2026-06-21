<?php
// emadrasah/modules/surat_masuk/create.php
$page_title = 'Registrasi Surat Masuk';
include __DIR__ . '/../../includes/header.php';

// Generate Nomor Agenda Otomatis (SM-YYYY-XXX)
$year = date('Y');
$stmt = $pdo->prepare("SELECT nomor_agenda FROM surat_masuk WHERE nomor_agenda LIKE ? ORDER BY id DESC LIMIT 1");
$stmt->execute(["SM-$year-%"]);
$last = $stmt->fetchColumn();

if ($last) {
    $num = (int)substr($last, -3) + 1;
} else {
    $num = 1;
}
$auto_agenda = "SM-$year-" . str_pad($num, 3, '0', STR_PAD_LEFT);
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title">Registrasi Surat Masuk</h2>
        <p class="page-subtitle">Masukkan detail surat yang baru diterima.</p>
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
            <div class="col-md-4">
                <label class="form-label small fw-bold">Nomor Agenda <span class="text-danger">*</span></label>
                <input type="text" name="nomor_agenda" class="form-control" required value="<?php echo $auto_agenda; ?>">
            </div>
            <div class="col-md-8">
                <label class="form-label small fw-bold">Asal Surat <span class="text-danger">*</span></label>
                <input type="text" name="asal_surat" class="form-control" required placeholder="Instansi/Perorangan">
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Nomor Surat</label>
                <input type="text" name="nomor_surat" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Perihal <span class="text-danger">*</span></label>
                <input type="text" name="perihal" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Tanggal Terima <span class="text-danger">*</span></label>
                <input type="date" name="tanggal_terima" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Tanggal Surat</label>
                <input type="date" name="tanggal_surat" class="form-control">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Status</label>
                <select name="status" class="form-select">
                    <option value="diterima">Diterima</option>
                    <option value="diproses">Diproses</option>
                    <option value="selesai">Selesai</option>
                </select>
            </div>
            <div class="col-md-12">
                <label class="form-label small fw-bold">Disposisi / Catatan</label>
                <textarea name="disposisi" class="form-control" rows="3"></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Upload Scan Surat (PDF/Gambar)</label>
                <input type="file" name="file_scan" class="form-control" accept=".pdf,image/*">
            </div>
        </div>

        <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary px-5 fw-bold">Simpan Surat Masuk</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>