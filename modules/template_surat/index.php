<?php
// emadrasah/modules/template_surat/index.php
$page_title = 'Manajemen Template Surat';
include __DIR__ . '/../../includes/header.php';

$stmt = $pdo->query("SELECT * FROM template_surat ORDER BY nama_template ASC");
$templates = $stmt->fetchAll();
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title"><i class="fas fa-file-contract me-2 text-primary"></i>Template Surat</h2>
        <p class="page-subtitle">Kelola master template surat untuk mempermudah persuratan.</p>
    </div>
    <div>
        <a href="create.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Template
        </a>
    </div>
</div>

<div class="row g-4 fade-in">
    <?php if (empty($templates)): ?>
    <div class="col-12 text-center py-5 text-muted bg-white rounded-4 border">
        <i class="fas fa-folder-open fa-3x mb-3 opacity-25"></i>
        <p>Belum ada template yang dibuat.</p>
    </div>
    <?php else: ?>
        <?php foreach ($templates as $t): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="fw-bold text-dark mb-2"><?php echo $t['nama_template']; ?></h5>
                    <p class="text-muted small mb-3">Dibuat pada: <?php echo date('d/m/Y', strtotime($t['created_at'])); ?></p>
                    <div class="btn-group w-100">
                        <a href="edit.php?id=<?php echo $t['id']; ?>" class="btn btn-outline-warning"><i class="fas fa-edit me-1"></i> Edit</a>
                        <a href="preview.php?id=<?php echo $t['id']; ?>" target="_blank" class="btn btn-outline-info"><i class="fas fa-eye me-1"></i> Preview</a>
                        <form action="delete.php" method="POST" class="d-inline" id="deleteForm-<?= $t['id'] ?>">
                            <?= csrf_input() ?>
                            <input type="hidden" name="id" value="<?= $t['id'] ?>">
                            <button type="button" class="btn btn-outline-danger" 
                                onclick="showConfirmModal('Hapus template ini?', function() { document.getElementById('deleteForm-<?= $t['id'] ?>').submit(); })">
                                <i class="fas fa-trash me-1"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>