<?php
// emadrasah/modules/kategori_sarana/index.php
$page_title = 'Kategori Sarana';
include __DIR__ . '/../../includes/header.php';

// Ambil semua data kategori
$stmt = $pdo->query("SELECT * FROM kategori_sarana ORDER BY nama_kategori ASC");
$kategori_list = $stmt->fetchAll();
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title"><i class="fas fa-tags me-2 text-primary"></i>Kategori Sarana Prasarana</h2>
        <p class="page-subtitle">Manajemen kategori untuk pengelompokan aset Madrasah.</p>
    </div>
    <div>
        <a href="create.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Kategori
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm overflow-hidden" style="max-width: 800px;">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3" style="width: 80px;">No</th>
                    <th class="py-3">Nama Kategori</th>
                    <th class="py-3">Keterangan</th>
                    <th class="px-4 py-3 text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($kategori_list)): ?>
                <tr>
                    <td colspan="4" class="text-center py-5 text-muted">Belum ada kategori yang ditambahkan.</td>
                </tr>
                <?php else: ?>
                    <?php $no=1; foreach ($kategori_list as $k): ?>
                    <tr>
                        <td class="px-4 text-center"><?php echo $no++; ?></td>
                        <td class="fw-bold text-primary"><?php echo $k['nama_kategori']; ?></td>
                        <td><small class="text-muted"><?php echo $k['deskripsi'] ?: '-'; ?></small></td>
                        <td class="px-4 text-end">
                            <div class="btn-group">
                                <a href="edit.php?id=<?php echo $k['id']; ?>" class="btn btn-sm btn-light border" title="Edit">
                                    <i class="fas fa-edit text-warning"></i>
                                </a>
                                <form action="delete.php" method="POST" class="d-inline" id="deleteForm-<?= $k['id'] ?>">
                                    <?= csrf_input() ?>
                                    <input type="hidden" name="id" value="<?= $k['id'] ?>">
                                    <button type="button" class="btn btn-sm btn-light border" 
                                        onclick="showConfirmModal('Hapus kategori ini?', function() { document.getElementById('deleteForm-<?= $k['id'] ?>').submit(); })" 
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
