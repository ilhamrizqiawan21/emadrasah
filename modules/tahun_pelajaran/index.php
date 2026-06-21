<?php
// emadrasah/modules/tahun_pelajaran/index.php
$page_title = 'Data Tahun Pelajaran';
include __DIR__ . '/../../includes/header.php';

// Ambil semua data tahun pelajaran
$stmt = $pdo->query("SELECT * FROM tahun_pelajaran ORDER BY kode DESC");
$tp_list = $stmt->fetchAll();
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title">Tahun Pelajaran</h2>
        <p class="page-subtitle">Manajemen periode tahun ajaran aktif Madrasah.</p>
    </div>
    <div>
        <a href="<?php echo base_url('modules/tahun_pelajaran/create.php'); ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Tahun Pelajaran
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3" style="width: 150px;">Kode</th>
                    <th class="py-3">Nama Tahun Pelajaran</th>
                    <th class="py-3 text-center">Status</th>
                    <th class="px-4 py-3 text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tp_list)): ?>
                <tr>
                    <td colspan="4" class="text-center py-5 text-muted">
                        <i class="fas fa-calendar-alt fa-3x mb-3 d-block opacity-25"></i>
                        Data tahun pelajaran belum tersedia.
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($tp_list as $tp): ?>
                    <tr>
                        <td class="px-4 fw-bold text-primary"><?php echo $tp['kode']; ?></td>
                        <td><?php echo $tp['nama']; ?></td>
                        <td class="text-center">
                            <?php if ($tp['is_aktif']): ?>
                                <span class="badge bg-success rounded-pill px-3">Aktif</span>
                            <?php else: ?>
                                <form action="<?= base_url('modules/tahun_pelajaran/activate.php') ?>" method="POST" class="d-inline" id="activateForm-<?= $tp['id'] ?>">
                                    <?= csrf_input() ?>
                                    <input type="hidden" name="id" value="<?= $tp['id'] ?>">
                                    <button type="button" class="badge bg-light text-muted border text-decoration-none px-3"
                                        style="cursor:pointer; font-size:0.75rem;"
                                        onclick="showConfirmModal('Aktifkan tahun pelajaran ini?', function() { document.getElementById('activateForm-<?= $tp['id'] ?>').submit(); })">Set Aktif</button>
                                </form>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 text-end">
                            <div class="btn-group">
                                <a href="<?php echo base_url('modules/tahun_pelajaran/edit.php?id=' . $tp['id']); ?>" class="btn btn-sm btn-light border" title="Edit">
                                    <i class="fas fa-edit text-warning"></i>
                                </a>
                                <form action="<?= base_url('modules/tahun_pelajaran/delete.php') ?>" method="POST" class="d-inline" id="deleteForm-<?= $tp['id'] ?>">
                                    <?= csrf_input() ?>
                                    <input type="hidden" name="id" value="<?= $tp['id'] ?>">
                                    <button type="button" class="btn btn-sm btn-light border" 
                                        onclick="showConfirmModal('Hapus data ini?', function() { document.getElementById('deleteForm-<?= $tp['id'] ?>').submit(); })" 
                                        title="Hapus">
                                        <i class="fas fa-trash text-danger"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>