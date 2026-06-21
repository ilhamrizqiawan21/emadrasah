<?php
// emadrasah/modules/kategori_sarana/edit.php
$page_title = 'Edit Kategori Sarana';
include __DIR__ . '/../../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM kategori_sarana WHERE id = ?");
$stmt->execute([$id]);
$k = $stmt->fetch();

if (!$k) {
    header('Location: index.php');
    exit;
}
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title">Edit Kategori</h2>
        <p class="page-subtitle">Memperbarui informasi kategori: <strong><?php echo $k['nama_kategori']; ?></strong></p>
    </div>
    <div>
        <a href="index.php" class="btn btn-light border">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm p-4 fade-in" style="max-width: 600px;">
    <form action="update.php" method="POST">
        <?php echo csrf_input(); ?>
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <div class="mb-3">
            <label class="form-label small fw-bold">Nama Kategori <span class="text-danger">*</span></label>
            <input type="text" name="nama_kategori" class="form-control" required value="<?php echo $k['nama_kategori']; ?>">
        </div>
        <div class="mb-3">
            <label class="form-label small fw-bold">Keterangan / Deskripsi</label>
            <textarea name="deskripsi" class="form-control" rows="3"><?php echo $k['deskripsi']; ?></textarea>
        </div>

        <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary px-5 fw-bold">Perbarui Kategori</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
