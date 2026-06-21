<?php
// emadrasah/modules/buku_induk/index.php
$page_title = 'Daftar Buku Induk Siswa';
include __DIR__ . '/../../includes/header.php';

// Fitur Pencarian
$search = isset($_GET['search']) ? input_safe($_GET['search']) : '';

// Query Dasar dengan Join Kelas
$query_str = "SELECT s.*, k.nama_kelas 
              FROM siswa s 
              LEFT JOIN kelas k ON s.kelas_id = k.id";

if ($search) {
    $query_str .= " WHERE s.nama_lengkap LIKE :search OR s.nis LIKE :search OR s.nisn LIKE :search";
}

$query_str .= " ORDER BY s.no_urut ASC";

$stmt = $pdo->prepare($query_str);
if ($search) {
    $stmt->execute(['search' => "%$search%"]); 
} else {
    $stmt->execute();
}
$siswa_list = $stmt->fetchAll();

// Helper: escape output
function h_idx($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title">Buku Induk Siswa</h2>
        <p class="page-subtitle">Manajemen data lengkap peserta didik MTs Al-Ihsan Batujajar.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo base_url('modules/buku_induk/create.php'); ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Siswa
        </a>
    </div>
</div>

<!-- Filter & Search -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Cari Nama, NIS, atau NISN..." value="<?php echo h_idx($search); ?>">
                    <button type="submit" class="btn btn-outline-primary">Cari</button>
                </div>
            </div>
            <?php if($search): ?>
            <div class="col-md-2">
                <a href="<?php echo base_url('modules/buku_induk/index.php'); ?>" class="btn btn-light w-100">Reset</a>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Table Data -->
<div class="card border-0 shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-center" style="width: 80px;">No. Urut</th>
                    <th class="py-3">Identitas Siswa</th>
                    <th class="py-3">L/P</th>
                    <th class="py-3">Kelas</th>
                    <th class="py-3">Status</th>
                    <th class="px-4 py-3 text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($siswa_list)): ?>
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">
                        <i class="fas fa-user-slash fa-3x mb-3 d-block opacity-25"></i>
                        Data siswa tidak ditemukan.
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($siswa_list as $s): ?>
                    <tr>
                        <td class="text-center fw-bold text-primary px-4"><?php echo h_idx($s['no_urut'] ?: '-'); ?></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="em-user-avatar me-3 bg-light text-primary">
                                    <span><?php echo strtoupper(substr(h_idx($s['nama_lengkap']), 0, 1)); ?></span>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark"><?php echo h_idx($s['nama_lengkap']); ?></div>
                                    <div class="small text-muted">NIS: <?php echo h_idx($s['nis']); ?> | NISN: <?php echo h_idx($s['nisn'] ?: '-'); ?></div>
                                </div>
                            </div>
                        </td>
                        <td><?php echo h_idx($s['jenis_kelamin']); ?></td>
                        <td>
                            <span class="badge bg-light text-primary border border-primary-subtle px-2 py-1">
                                <?php echo h_idx($s['nama_kelas'] ?: 'Belum Diatur'); ?>
                            </span>
                        </td>
                        <td>
                            <?php 
                                $status_class = [
                                    'Aktif' => 'bg-success-subtle text-success',
                                    'Lulus' => 'bg-primary-subtle text-primary',
                                    'Pindah' => 'bg-warning-subtle text-warning',
                                    'Keluar' => 'bg-danger-subtle text-danger'
                                ];
                                $cls = $status_class[$s['status']] ?? 'bg-secondary-subtle';
                            ?>
                            <span class="badge <?php echo $cls; ?> rounded-pill px-3"><?php echo h_idx($s['status']); ?></span>
                        </td>
                        <td class="px-4 text-end">
                            <div class="btn-group">
                                <a href="<?php echo base_url('modules/buku_induk/show.php?id=' . $s['id']); ?>" class="btn btn-sm btn-light border" title="Detail">
                                    <i class="fas fa-eye text-primary"></i>
                                </a>
                                <a href="<?php echo base_url('modules/buku_induk/edit.php?id=' . $s['id']); ?>" class="btn btn-sm btn-light border" title="Edit">
                                    <i class="fas fa-edit text-warning"></i>
                                </a>
                                <form action="<?= base_url('modules/buku_induk/delete.php') ?>" method="POST" class="d-inline" id="deleteForm-<?= $s['id'] ?>">
                                    <?= csrf_input() ?>
                                    <input type="hidden" name="id" value="<?= $s['id'] ?>">
                                    <button type="button" class="btn btn-sm btn-light border" 
                                        onclick="showConfirmModal('Hapus data siswa ini?', function() { document.getElementById('deleteForm-<?= $s['id'] ?>').submit(); })" 
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
