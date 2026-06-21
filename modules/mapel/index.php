<?php
// emadrasah/modules/mapel/index.php
$page_title = 'Data Mata Pelajaran';
include __DIR__ . '/../../includes/header.php';

// Fitur Pencarian
$search = isset($_GET['search']) ? input_safe($_GET['search']) : '';

// Query Dasar
$query_str = "SELECT * FROM mapels";
if ($search) {
    $query_str .= " WHERE nama_mapel LIKE :search";
}
$query_str .= " ORDER BY nama_mapel ASC";

$stmt = $pdo->prepare($query_str);
if ($search) {
    $stmt->execute(['search' => "%$search%"]);
} else {
    $stmt->execute();
}
$mapel_list = $stmt->fetchAll();
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title">Mata Pelajaran</h2>
        <p class="page-subtitle">Manajemen daftar mata pelajaran MTs Al-Ihsan.</p>
    </div>
    <div>
        <a href="<?php echo base_url('modules/mapel/create.php'); ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Mapel
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
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Cari Nama Mata Pelajaran..." value="<?php echo htmlspecialchars($search); ?>">
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
                    <th class="px-4 py-3" style="width: 80px;">No</th>
                    <th class="py-3">Nama Mata Pelajaran</th>
                    <th class="px-4 py-3 text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($mapel_list)): ?>
                <tr>
                    <td colspan="3" class="text-center py-5 text-muted">
                        <i class="fas fa-book fa-3x mb-3 d-block opacity-25"></i>
                        Data mata pelajaran belum tersedia.
                    </td>
                </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($mapel_list as $m): ?>
                    <tr>
                        <td class="px-4 text-center"><?php echo $no++; ?></td>
                        <td class="fw-bold text-primary"><?php echo htmlspecialchars($m['nama_mapel']); ?></td>
                        <td class="px-4 text-end">
                            <div class="btn-group">
                                <a href="<?php echo base_url('modules/mapel/edit.php?id=' . (int)$m['id']); ?>" class="btn btn-sm btn-light border" title="Edit">
                                    <i class="fas fa-edit text-warning"></i>
                                </a>
                                <form action="<?php echo base_url('modules/mapel/delete.php'); ?>" method="POST" class="d-inline" id="deleteMapelForm-<?php echo $m['id']; ?>">
                                    <?php echo csrf_input(); ?>
                                    <input type="hidden" name="id" value="<?php echo $m['id']; ?>">
                                    <button type="button" class="btn btn-sm btn-light border" onclick="showConfirmModal('Apakah Anda yakin ingin menghapus mata pelajaran <strong><?php echo htmlspecialchars($m['nama_mapel']); ?></strong>?', function() { document.getElementById('deleteMapelForm-<?php echo $m['id']; ?>').submit(); })" title="Hapus">
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
