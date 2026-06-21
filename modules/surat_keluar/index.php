<?php
// emadrasah/modules/surat_keluar/index.php
$page_title = 'Surat Keluar';
include __DIR__ . '/../../includes/header.php';

// Fitur Pencarian
$search = isset($_GET['search']) ? input_safe($_GET['search']) : '';

// Query Dasar
$hasTemplateColumn = table_has_column($pdo, 'surat_keluar', 'template_id');
if ($hasTemplateColumn) {
    $query_str = "SELECT sk.*, ts.nama_template FROM surat_keluar sk LEFT JOIN template_surat ts ON sk.template_id = ts.id";
} else {
    $query_str = "SELECT sk.* FROM surat_keluar sk";
}
if ($search) {
    $query_str .= " WHERE sk.nomor_surat LIKE :search OR sk.tujuan LIKE :search OR sk.perihal LIKE :search";
}
$query_str .= " ORDER BY sk.tanggal_kirim DESC, sk.nomor_surat DESC";

$stmt = $pdo->prepare($query_str);
if ($search) {
    $stmt->execute(['search' => "%$search%"]);
} else {
    $stmt->execute();
}
$surat_list = $stmt->fetchAll();
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title"><i class="fas fa-paper-plane me-2 text-primary"></i>Surat Keluar</h2>
        <p class="page-subtitle">Pencatatan surat yang dikirim oleh Madrasah.</p>
    </div>
    <div>
        <a href="create.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Registrasi Surat Keluar
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Cari Nomor, Tujuan, atau Perihal..." value="<?php echo $search; ?>">
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
                    <th class="px-4 py-3">Nomor Surat</th>
                    <th class="py-3">Tujuan</th>
                    <th class="py-3">Perihal</th>
                    <th class="py-3">Tgl Kirim</th>
                    <th class="py-3">Lampiran</th>
                    <th class="px-4 py-3 text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($surat_list)): ?>
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">Data surat keluar belum tersedia.</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($surat_list as $s): ?>
                    <tr>
                        <td class="px-4 fw-bold text-primary"><?php echo $s['nomor_surat']; ?></td>
                        <td class="fw-bold"><?php echo $s['tujuan']; ?></td>
                        <td><?php echo $s['perihal']; ?></td>
                        <td><?php echo tgl_indo($s['tanggal_kirim']); ?></td>
                        <td><small><?php echo $s['lampiran'] ?: '-'; ?></small></td>
                        <td class="px-4 text-end">
                            <div class="btn-group">
                                <?php if (isset($s['template_id']) && $s['template_id']): ?>
                                <a href="cetak.php?id=<?php echo $s['id']; ?>" class="btn btn-sm btn-outline-success" target="_blank" title="Cetak dari Template">
                                    <i class="fas fa-print"></i>
                                </a>
                                <?php endif; ?>
                                <?php if($s['file_draft']): ?>
                                <a href="<?php echo base_url('uploads/surat_keluar/' . $s['file_draft']); ?>" class="btn btn-sm btn-light border" target="_blank" title="Lihat Draft">
                                    <i class="fas fa-file-pdf text-danger"></i>
                                </a>
                                <?php endif; ?>
                                <a href="show.php?id=<?php echo $s['id']; ?>" class="btn btn-sm btn-light border" title="Detail">
                                    <i class="fas fa-eye text-primary"></i>
                                </a>
                                <a href="edit.php?id=<?php echo $s['id']; ?>" class="btn btn-sm btn-light border" title="Edit">
                                    <i class="fas fa-edit text-warning"></i>
                                </a>
                                <form action="delete.php" method="POST" class="d-inline" id="deleteForm-<?= $s['id'] ?>">
                                    <?= csrf_input() ?>
                                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                    <button type="button" class="btn btn-sm btn-light border" 
                                        onclick="showConfirmModal('Hapus data surat ini?', function() { document.getElementById('deleteForm-<?= $s['id'] ?>').submit(); })" 
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
