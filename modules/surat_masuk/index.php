<?php
// emadrasah/modules/surat_masuk/index.php
$page_title = 'Surat Masuk';
include __DIR__ . '/../../includes/header.php';

// Fitur Pencarian
$search = isset($_GET['search']) ? input_safe($_GET['search']) : '';

// Query Dasar
$query_str = "SELECT * FROM surat_masuk";
if ($search) {
    $query_str .= " WHERE nomor_agenda LIKE :search OR asal_surat LIKE :search OR perihal LIKE :search";
}
$query_str .= " ORDER BY tanggal_terima DESC, nomor_agenda DESC";

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
        <h2 class="page-title"><i class="fas fa-envelope-open-text me-2 text-primary"></i>Surat Masuk</h2>
        <p class="page-subtitle">Pencatatan dan arsip surat yang diterima Madrasah.</p>
    </div>
    <div>
        <a href="create.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Registrasi Surat Masuk
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Cari Agenda, Asal, atau Perihal..." value="<?php echo $search; ?>">
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
                    <th class="px-4 py-3">No. Agenda</th>
                    <th class="py-3">Asal & Nomor Surat</th>
                    <th class="py-3">Perihal</th>
                    <th class="py-3">Tgl Terima</th>
                    <th class="py-3">Status</th>
                    <th class="px-4 py-3 text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($surat_list)): ?>
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">Data surat masuk belum tersedia.</td>
                </tr>
                <?php else: ?>
                    <?php foreach ($surat_list as $s): ?>
                    <tr>
                        <td class="px-4 fw-bold text-primary"><?php echo $s['nomor_agenda']; ?></td>
                        <td>
                            <div class="fw-bold"><?php echo $s['asal_surat']; ?></div>
                            <small class="text-muted"><?php echo $s['nomor_surat'] ?: '-'; ?></small>
                        </td>
                        <td><?php echo $s['perihal']; ?></td>
                        <td><?php echo tgl_indo($s['tanggal_terima']); ?></td>
                        <td>
                            <?php 
                                $status_badge = [
                                    'diterima' => 'bg-info-subtle text-info',
                                    'diproses' => 'bg-warning-subtle text-warning',
                                    'selesai' => 'bg-success-subtle text-success'
                                ];
                                $cls = $status_badge[$s['status']] ?? 'bg-secondary-subtle';
                            ?>
                            <span class="badge <?php echo $cls; ?> rounded-pill px-3"><?php echo ucfirst($s['status']); ?></span>
                        </td>
                        <td class="px-4 text-end">
                            <div class="btn-group">
                                <?php if($s['file_scan']): ?>
                                <a href="<?php echo base_url('uploads/surat_masuk/' . $s['file_scan']); ?>" class="btn btn-sm btn-light border" target="_blank" title="Lihat Scan">
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
                                        onclick="showConfirmModal('Hapus data surat ini?', function() { document.getElementById('deleteForm-<?= $s['id'] ?>').submit(); })" title="Hapus">
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