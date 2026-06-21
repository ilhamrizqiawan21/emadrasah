<?php
// emadrasah/modules/surat_keluar/edit.php
$page_title = 'Ubah Surat Keluar';
include __DIR__ . '/../../includes/header.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM surat_keluar WHERE id = ?");
$stmt->execute([$id]);
$surat = $stmt->fetch();

if (!$surat) {
    header('Location: index.php');
    exit;
}
$hasTemplateColumn = table_has_column($pdo, 'surat_keluar', 'template_id');
$templates = $hasTemplateColumn ? $pdo->query("SELECT id, nama_template FROM template_surat ORDER BY nama_template ASC")->fetchAll() : [];
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title">Ubah Surat Keluar</h2>
        <p class="page-subtitle">Perbarui detail dan template surat keluar.</p>
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
            <?php if ($hasTemplateColumn): ?>
            <div class="col-md-12 mb-2">
                <label class="form-label small fw-bold text-primary">Gunakan Template (Opsional)</label>
                <select name="template_id" class="form-select border-primary-subtle bg-light-subtle">
                    <option value="">-- Pilih Template Jika Ingin Cetak Otomatis --</option>
                    <?php foreach($templates as $t): ?>
                    <option value="<?php echo $t['id']; ?>" <?php echo isset($surat['template_id']) && $surat['template_id'] == $t['id'] ? 'selected' : ''; ?>><?php echo $t['nama_template']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Nomor Surat <span class="text-danger">*</span></label>
                <input type="text" name="nomor_surat" class="form-control" required value="<?php echo $surat['nomor_surat']; ?>">
            </div>
            <div class="col-md-8">
                <label class="form-label small fw-bold">Tujuan Surat <span class="text-danger">*</span></label>
                <input type="text" name="tujuan" class="form-control" required value="<?php echo $surat['tujuan']; ?>">
            </div>
            <div class="col-md-12">
                <label class="form-label small fw-bold">Perihal <span class="text-danger">*</span></label>
                <input type="text" name="perihal" class="form-control" required value="<?php echo $surat['perihal']; ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Tanggal Kirim <span class="text-danger">*</span></label>
                <input type="date" name="tanggal_kirim" class="form-control" required value="<?php echo $surat['tanggal_kirim']; ?>">
            </div>
            <div class="col-md-8">
                <label class="form-label small fw-bold">Lampiran</label>
                <input type="text" name="lampiran" class="form-control" value="<?php echo $surat['lampiran']; ?>" placeholder="Contoh: 1 Berkas">
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Upload Draft/Scan Surat (PDF/Gambar)</label>
                <input type="file" name="file_draft" class="form-control" accept=".pdf,image/*">
                <?php if ($surat['file_draft']): ?>
                <div class="form-text">File saat ini: <a href="<?php echo base_url('uploads/surat_keluar/' . $surat['file_draft']); ?>" target="_blank"><?php echo $surat['file_draft']; ?></a></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary px-5 fw-bold">Simpan Perubahan</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
