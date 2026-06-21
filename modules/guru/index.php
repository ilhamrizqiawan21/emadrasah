<?php
$page_title = 'Data Guru';
include __DIR__ . '/../../includes/header.php';

$search = isset($_GET['search']) ? input_safe($_GET['search']) : '';

$query_str = "SELECT * FROM gurus";
if ($search) {
    $query_str .= " WHERE nama LIKE :search OR kode LIKE :search OR nip LIKE :search";
}
$query_str .= " ORDER BY nama ASC";

$stmt = $pdo->prepare($query_str);
if ($search) {
    $stmt->execute(['search' => "%$search%"]);
} else {
    $stmt->execute();
}
$guru_list = $stmt->fetchAll();
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title">Data Guru</h2>
        <p class="page-subtitle">Manajemen tenaga pendidik MTs Al-Ihsan.</p>
    </div>
    <div>
        <a href="<?php echo base_url('guru/create'); ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Guru
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Cari Nama, Kode, atau NIP..." value="<?= $search ?>">
                    <button type="submit" class="btn btn-outline-primary">Cari</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3" style="width: 100px;">Kode</th>
                    <th class="py-3">Nama Lengkap</th>
                    <th class="py-3">NIP</th>
                    <th class="py-3">Bidang Studi</th>
                    <th class="py-3">Status</th>
                    <th class="px-4 py-3 text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($guru_list)): ?>
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="fas fa-chalkboard-user fa-3x mb-3 d-block opacity-25"></i>
                        Data guru belum tersedia.
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($guru_list as $g): ?>
                    <tr>
                        <td data-label="Kode" class="px-4 fw-bold text-primary"><?= htmlspecialchars($g['kode']) ?></td>
                        <td data-label="Nama Lengkap">
                            <div class="fw-bold text-dark"><?= htmlspecialchars($g['nama']) ?></div>
                            <div class="small text-muted"><?= $g['email'] ?: 'No Email' ?> | <?= $g['phone'] ?: '-' ?></div>
                        </td>
                        <td data-label="NIP"><?= $g['nip'] ?: '-' ?></td>
                        <td data-label="Bidang Studi"><?= $g['bidang_studi'] ?: '-' ?></td>
                        <td data-label="Status">
                            <span class="badge <?= ($g['status'] == 'aktif') ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' ?> rounded-pill px-3">
                                <?= ucfirst($g['status'] ?? 'aktif') ?>
                            </span>
                        </td>
                        <td data-label="Aksi" class="px-4 text-end">
                            <div class="btn-group">
                                <a href="<?= base_url('guru/edit.php?id=' . $g['id']) ?>" class="btn btn-sm btn-light border" title="Edit">
                                    <i class="fas fa-edit text-warning"></i>
                                </a>
                                <form action="<?= base_url('guru/delete.php') ?>" method="POST" class="d-inline" id="deleteForm-<?= $g['id'] ?>">
                                    <?= csrf_input() ?>
                                    <input type="hidden" name="id" value="<?= $g['id'] ?>">
                                    <button type="button" class="btn btn-sm btn-light border" 
                                        onclick="showConfirmModal('Apakah Anda yakin ingin menghapus data guru <strong><?= htmlspecialchars($g['nama']) ?></strong>? Aksi ini tidak dapat dibatalkan.', function() { document.getElementById('deleteForm-<?= $g['id'] ?>').submit(); })" 
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