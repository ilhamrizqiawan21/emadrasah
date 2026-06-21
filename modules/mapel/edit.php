<?php
// emadrasah/modules/mapel/edit.php
$page_title = 'Edit Mata Pelajaran';
include __DIR__ . '/../../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM mapels WHERE id = ?");
$stmt->execute([$id]);
$m = $stmt->fetch();

if (!$m) {
    set_flash('danger', 'Data mata pelajaran tidak ditemukan.');
    header('Location: ' . base_url('modules/mapel/index.php'));
    exit;
}
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title">Edit Mata Pelajaran</h2>
        <p class="page-subtitle">Memperbarui informasi: <strong><?php echo htmlspecialchars($m['nama_mapel']); ?></strong></p>
    </div>
    <div>
        <a href="<?php echo base_url('modules/mapel/index.php'); ?>" class="btn btn-light border">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm p-4 fade-in" style="max-width: 600px;">
    <form action="<?php echo base_url('modules/mapel/update.php'); ?>" method="POST">
        <?php echo csrf_input(); ?>
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <div class="mb-3">
            <label class="form-label">Nama Mata Pelajaran <span class="text-danger">*</span></label>
            <input type="text" name="nama_mapel" class="form-control" required value="<?php echo htmlspecialchars($m['nama_mapel']); ?>">
        </div>

        <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary px-5 fw-bold">Perbarui Mata Pelajaran</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
