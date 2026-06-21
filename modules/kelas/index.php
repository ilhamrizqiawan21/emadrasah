<?php
// emadrasah/modules/kelas/index.php
$page_title = 'Data Kelas';
include __DIR__ . '/../../includes/header.php';

// Fitur Pencarian
$search = isset($_GET['search']) ? input_safe($_GET['search']) : '';

// Query Dasar dengan Join Guru Pembimbing (Wali Kelas)
$query_str = "SELECT k.*, g.nama as nama_guru 
              FROM kelas k 
              LEFT JOIN gurus g ON k.guru_pembimbing_id = g.id";

if ($search) {
    $query_str .= " WHERE k.nama_kelas LIKE :search OR g.nama LIKE :search";
}
$query_str .= " ORDER BY k.tingkat ASC, k.nama_kelas ASC";

$stmt = $pdo->prepare($query_str);
if ($search) {
    $stmt->execute(['search' => "%$search%"]);
} else {
    $stmt->execute();
}
$kelas_list = $stmt->fetchAll();
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title">Data Kelas</h2>
        <p class="page-subtitle">Manajemen data kelas dan wali kelas MTs Al-Ihsan.</p>
    </div>
    <div>
        <a href="create.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Kelas
        </a>
    </div>
</div>

<!-- Search -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Cari Nama Kelas atau Wali Kelas..." value="<?php echo $search; ?>">
                    <button type="submit" class="btn btn-outline-primary">Cari</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Table Data -->
<div class="card border-0 shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3" style="width: 150px;">Tingkat</th>
                    <th class="py-3">Nama Kelas</th>
                    <th class="py-3">Wali Kelas</th>
                    <th class="py-3 text-center">Kapasitas</th>
                    <th class="py-3">Ruangan</th>
                    <th class="px-4 py-3 text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($kelas_list)): ?>
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="fas fa-door-open fa-3x mb-3 d-block opacity-25"></i>
                        Data kelas belum tersedia.
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($kelas_list as $k): ?>
                    <tr>
                        <td class="px-4 fw-bold">Tingkat <?php echo $k['tingkat']; ?></td>
                        <td class="fw-bold text-primary"><?php echo $k['nama_kelas']; ?></td>
                        <td><?php echo $k['nama_guru'] ?: '<span class="text-muted small"><em>Belum ditentukan</em></span>'; ?></td>
                        <td class="text-center"><?php echo $k['kapasitas'] ?: '-'; ?></td>
                        <td><?php echo $k['ruangan'] ?: '-'; ?></td>
                        <td class="px-4 text-end">
                            <div class="btn-group">
                                <a href="edit.php?id=<?php echo $k['id']; ?>" class="btn btn-sm btn-light border" title="Edit">
                                    <i class="fas fa-edit text-warning"></i>
                                </a>
                                <form action="delete.php" method="POST" class="d-inline" id="deleteForm-<?= $k['id'] ?>">
                                    <?= csrf_input() ?>
                                    <input type="hidden" name="id" value="<?= $k['id'] ?>">
                                    <button type="button" class="btn btn-sm btn-light border"
                                        onclick="showConfirmModal('Hapus data kelas ini?', function() { document.getElementById('deleteForm-<?= $k['id'] ?>').submit(); })" title="Hapus">
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