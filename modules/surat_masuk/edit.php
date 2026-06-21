<?php
// emadrasah/modules/surat_masuk/edit.php
$page_title = 'Ubah Surat Masuk';
include __DIR__ . '/../../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM surat_masuk WHERE id = ?");
$stmt->execute([$id]);
$surat = $stmt->fetch();

if (!$surat) {
    header('Location: index.php');
    exit;
}
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title">Ubah Surat Masuk</h2>
        <p class="page-subtitle">Perbarui data dan scan surat masuk.</p>
    </div>
    <div>
        <a href="index.php" class="btn btn-light border">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm p-4 fade-in">
    <form action="update.php" method="POST" enctype="multipart/form-data">
        <?php echo csrf_input(); ?>
        <input type="hidden" name="id" value="<?php echo $surat['id']; ?>">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label small fw-bold">Nomor Agenda <span class="text-danger">*</span></label>
                <input type="text" name="nomor_agenda" class="form-control" required value="<?php echo $surat['nomor_agenda']; ?>">
            </div>
            <div class="col-md-8">
                <label class="form-label small fw-bold">Asal Surat <span class="text-danger">*</span></label>
                <input type="text" name="asal_surat" class="form-control" required value="<?php echo $surat['asal_surat']; ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Nomor Surat</label>
                <input type="text" name="nomor_surat" class="form-control" value="<?php echo $surat['nomor_surat']; ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Perihal <span class="text-danger">*</span></label>
                <input type="text" name="perihal" class="form-control" required value="<?php echo $surat['perihal']; ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Tanggal Terima <span class="text-danger">*</span></label>
                <input type="date" name="tanggal_terima" class="form-control" required value="<?php echo $surat['tanggal_terima']; ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Tanggal Surat</label>
                <input type="date" name="tanggal_surat" class="form-control" value="<?php echo $surat['tanggal_surat']; ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Status</label>
                <select name="status" class="form-select">
                    <option value="diterima" <?php echo $surat['status'] == 'diterima' ? 'selected' : ''; ?>>Diterima</option>
                    <option value="diproses" <?php echo $surat['status'] == 'diproses' ? 'selected' : ''; ?>>Diproses</option>
                    <option value="selesai" <?php echo $surat['status'] == 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                </select>
            </div>
            <div class="col-md-12">
                <label class="form-label small fw-bold">Disposisi / Catatan</label>
                <textarea name="disposisi" class="form-control" rows="3"><?php echo $surat['disposisi']; ?></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Upload Scan Surat (PDF/Gambar)</label>
                <input type="file" name="file_scan" class="form-control" accept=".pdf,image/*">
                <?php if ($surat['file_scan']): ?>
                <div class="form-text">File saat ini: <a href="<?php echo base_url('uploads/surat_masuk/' . $surat['file_scan']); ?>" target="_blank"><?php echo $surat['file_scan']; ?></a></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary px-5 fw-bold">Simpan Perubahan</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
