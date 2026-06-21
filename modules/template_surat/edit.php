<?php
// emadrasah/modules/template_surat/edit.php
$page_title = 'Ubah Template Surat';
include __DIR__ . '/../../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM template_surat WHERE id = ?");
$stmt->execute([$id]);
$template = $stmt->fetch();

if (!$template) {
    header('Location: index.php');
    exit;
}
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title">Ubah Template Surat</h2>
        <p class="page-subtitle">Perbarui nama dan konten template.</p>
    </div>
    <div>
        <a href="index.php" class="btn btn-light border">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm p-4 fade-in">
    <form action="update.php" method="POST">
        <?php echo csrf_input(); ?>
        <input type="hidden" name="id" value="<?php echo $template['id']; ?>">
        <div class="mb-3">
            <label class="form-label small fw-bold">Nama Template <span class="text-danger">*</span></label>
            <input type="text" name="nama_template" class="form-control" required value="<?php echo $template['nama_template']; ?>">
        </div>
        <div class="mb-4">
            <label class="form-label small fw-bold">Konten Template (HTML) <span class="text-danger">*</span></label>
            <textarea name="konten" class="form-control" rows="15"><?php echo htmlspecialchars($template['konten']); ?></textarea>
            <div class="form-text mt-2">
                Placeholder: <code class="bg-light px-1">{nomor_surat}</code>, <code class="bg-light px-1">{tanggal}</code>, <code class="bg-light px-1">{tujuan}</code>, <code class="bg-light px-1">{perihal}</code>.
            </div>
        </div>
        <div class="text-end">
            <button type="submit" class="btn btn-primary px-5 fw-bold">Simpan Perubahan</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
