<?php
// emadrasah/modules/tahun_pelajaran/edit.php
$page_title = 'Edit Tahun Pelajaran';
include __DIR__ . '/../../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM tahun_pelajaran WHERE id = ?");
$stmt->execute([$id]);
$tp = $stmt->fetch();

if (!$tp) {
    set_flash('danger', 'Data tahun pelajaran tidak ditemukan.');
    header('Location: ' . base_url('modules/tahun_pelajaran/index.php'));
    exit;
}
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title">Edit Tahun Pelajaran</h2>
        <p class="page-subtitle">Memperbarui periode tahun ajaran: <strong><?php echo htmlspecialchars($tp['kode']); ?></strong></p>
    </div>
    <div>
        <a href="<?php echo base_url('modules/tahun_pelajaran/index.php'); ?>" class="btn btn-light border">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm p-4 fade-in" style="max-width: 600px;">
    <form action="<?php echo base_url('modules/tahun_pelajaran/update.php'); ?>" method="POST">
        <?php echo csrf_input(); ?>
        <input type="hidden" name="id" value="<?php echo $tp['id']; ?>">
        
        <div class="mb-3">
            <label class="form-label">Kode <span class="text-danger">*</span></label>
            <input type="text" name="kode" class="form-control" required placeholder="Contoh: 2024/2025" value="<?php echo htmlspecialchars($tp['kode']); ?>">
            <small class="text-muted">Gunakan format YYYY/YYYY.</small>
        </div>
        <div class="mb-3">
            <label class="form-label">Nama Tahun Pelajaran <span class="text-danger">*</span></label>
            <input type="text" name="nama" class="form-control" required placeholder="Contoh: Tahun Pelajaran 2024/2025" value="<?php echo htmlspecialchars($tp['nama']); ?>">
        </div>
        <div class="mb-3">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="is_aktif" id="isAktif" value="1" <?php echo $tp['is_aktif'] ? 'checked' : ''; ?>>
                <label class="form-check-label" for="isAktif">Set sebagai Tahun Pelajaran Aktif</label>
            </div>
        </div>

        <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary px-5 fw-bold">Simpan Perubahan</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
