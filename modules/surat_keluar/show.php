<?php
// emadrasah/modules/surat_keluar/show.php
$page_title = 'Detail Surat Keluar';
include __DIR__ . '/../../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$hasTemplateColumn = table_has_column($pdo, 'surat_keluar', 'template_id');
if ($hasTemplateColumn) {
    $stmt = $pdo->prepare("SELECT sk.*, ts.nama_template FROM surat_keluar sk LEFT JOIN template_surat ts ON sk.template_id = ts.id WHERE sk.id = ?");
} else {
    $stmt = $pdo->prepare("SELECT sk.* FROM surat_keluar sk WHERE sk.id = ?");
}
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
        <h2 class="page-title">Detail Surat Keluar</h2>
        <p class="page-subtitle">Nomor Surat: <?php echo $surat['nomor_surat']; ?></p>
    </div>
    <div>
        <a href="index.php" class="btn btn-light border">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm p-4 fade-in">
    <div class="row g-4">
        <div class="col-md-6">
            <div class="mb-3"><strong>Tujuan</strong><div><?php echo $surat['tujuan']; ?></div></div>
            <div class="mb-3"><strong>Perihal</strong><div><?php echo $surat['perihal']; ?></div></div>
            <div class="mb-3"><strong>Tanggal Kirim</strong><div><?php echo tgl_indo($surat['tanggal_kirim']); ?></div></div>
            <div class="mb-3"><strong>Template</strong><div><?php echo isset($surat['nama_template']) && $surat['nama_template'] ? $surat['nama_template'] : '-'; ?></div></div>
        </div>
        <div class="col-md-6">
            <div class="mb-3"><strong>Lampiran</strong><div><?php echo $surat['lampiran'] ?: '-'; ?></div></div>
            <?php if (!empty($surat['file_draft'])): ?>
            <div class="mb-3"><strong>File Draft</strong><div><a href="<?php echo base_url('uploads/surat_keluar/' . $surat['file_draft']); ?>" class="btn btn-sm btn-outline-primary" target="_blank"><?php echo $surat['file_draft']; ?></a></div></div>
            <?php endif; ?>
        </div>
    </div>
    <div class="mt-4">
        <a href="edit.php?id=<?php echo $surat['id']; ?>" class="btn btn-primary me-2">Ubah</a>
        <form action="delete.php" method="POST" class="d-inline" id="deleteForm-<?= $surat['id'] ?>">
            <?= csrf_input() ?>
            <input type="hidden" name="id" value="<?= $surat['id'] ?>">
            <button type="button" class="btn btn-outline-danger" 
                onclick="showConfirmModal('Hapus surat keluar ini?', function() { document.getElementById('deleteForm-<?= $surat['id'] ?>').submit(); })">Hapus</button>
        </form>
        <?php if (isset($surat['template_id']) && $surat['template_id']): ?>
        <a href="cetak.php?id=<?php echo $surat['id']; ?>" class="btn btn-outline-success" target="_blank">Cetak</a>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
