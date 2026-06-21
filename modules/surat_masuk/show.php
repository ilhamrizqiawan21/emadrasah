<?php
// emadrasah/modules/surat_masuk/show.php
$page_title = 'Detail Surat Masuk';
include __DIR__ . '/../../includes/header.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
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
        <h2 class="page-title">Detail Surat Masuk</h2>
        <p class="page-subtitle">Nomor Agenda: <?php echo $surat['nomor_agenda']; ?></p>
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
            <div class="mb-3"><strong>Asal Surat</strong><div><?php echo $surat['asal_surat']; ?></div></div>
            <div class="mb-3"><strong>Nomor Surat</strong><div><?php echo $surat['nomor_surat'] ?: '-'; ?></div></div>
            <div class="mb-3"><strong>Perihal</strong><div><?php echo $surat['perihal']; ?></div></div>
            <div class="mb-3"><strong>Tanggal Terima</strong><div><?php echo tgl_indo($surat['tanggal_terima']); ?></div></div>
            <div class="mb-3"><strong>Tanggal Surat</strong><div><?php echo $surat['tanggal_surat'] ? tgl_indo($surat['tanggal_surat']) : '-'; ?></div></div>
        </div>
        <div class="col-md-6">
            <div class="mb-3"><strong>Status</strong><div><?php echo ucfirst($surat['status']); ?></div></div>
            <div class="mb-3"><strong>Disposisi</strong><div><?php echo nl2br($surat['disposisi']); ?></div></div>
            <?php if ($surat['file_scan']): ?>
            <div class="mb-3"><strong>Scan Surat</strong><div><a href="<?php echo base_url('uploads/surat_masuk/' . $surat['file_scan']); ?>" class="btn btn-sm btn-outline-primary" target="_blank"><?php echo $surat['file_scan']; ?></a></div></div>
            <?php endif; ?>
        </div>
    </div>
    <div class="mt-4">
        <a href="edit.php?id=<?php echo $surat['id']; ?>" class="btn btn-primary me-2">Ubah</a>
        <form action="delete.php" method="POST" class="d-inline" id="deleteForm-<?= $surat['id'] ?>">
            <?= csrf_input() ?>
            <input type="hidden" name="id" value="<?= $surat['id'] ?>">
            <button type="button" class="btn btn-outline-danger" 
                onclick="showConfirmModal('Hapus surat masuk ini?', function() { document.getElementById('deleteForm-<?= $surat['id'] ?>').submit(); })">
                Hapus
            </button>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
