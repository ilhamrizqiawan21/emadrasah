<?php
// emadrasah/modules/siswa/index.php
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
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title">Buku Induk Siswa</h2>
        <p class="page-subtitle">Manajemen data lengkap peserta didik MTs Al-Ihsan.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="export_excel.php" class="btn btn-outline-success">
            <i class="fas fa-file-excel me-2"></i>Export Excel
        </a>
        <a href="export_pdf.php" target="_blank" class="btn btn-outline-danger">
            <i class="fas fa-file-pdf me-2"></i>Export PDF
        </a>
        <a href="import_excel.php" class="btn btn-outline-primary">
            <i class="fas fa-upload me-2"></i>Import Bulk
        </a>
        <a href="create.php" class="btn btn-primary">
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
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Cari Nama, NIS, atau NISN..." value="<?php echo $search; ?>">
                    <button type="submit" class="btn btn-outline-primary">Cari</button>
                </div>
            </div>
            <?php if($search): ?>
            <div class="col-md-2">
                <a href="index.php" class="btn btn-light w-100">Reset</a>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>

<!-- Table Data -->
<div class="card border-0 shadow-sm overflow-hidden fade-in">
    <div class="table-responsive p-3">
        <table class="table table-hover align-middle mb-0" id="siswaTable">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-3 text-center" style="width: 80px;">No. Urut</th>
                    <th class="py-3">Identitas Siswa</th>
                    <th class="py-3">L/P</th>
                    <th class="py-3">Kelas</th>
                    <th class="py-3">Status</th>
                    <th class="px-3 py-3 text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($siswa_list as $s): ?>
                <tr>
                    <td data-label="No. Urut" class="text-center fw-bold text-primary px-3"><?php echo $s['no_urut'] ?: '-'; ?></td>
                    <td data-label="Identitas Siswa">
                        <div class="d-flex align-items-center">
                            <div class="em-user-avatar me-3 bg-light text-primary">
                                <span><?php echo strtoupper(substr($s['nama_lengkap'], 0, 1)); ?></span>
                            </div>
                            <div class="text-start">
                                <div class="fw-bold text-dark"><?php echo $s['nama_lengkap']; ?></div>
                                <div class="small text-muted">NIS: <?php echo $s['nis']; ?> | NISN: <?php echo $s['nisn'] ?: '-'; ?></div>
                            </div>
                        </div>
                    </td>
                    <td data-label="L/P"><?php echo $s['jenis_kelamin']; ?></td>
                    <td data-label="Kelas">
                        <span class="badge bg-light text-primary border border-primary-subtle px-2 py-1">
                            <?php echo $s['nama_kelas'] ?: 'Belum Diatur'; ?>
                        </span>
                    </td>
                    <td data-label="Status">
                        <?php 
                            $status_class = [
                                'Aktif' => 'bg-success-subtle text-success',
                                'Lulus' => 'bg-primary-subtle text-primary',
                                'Pindah' => 'bg-warning-subtle text-warning',
                                'Keluar' => 'bg-danger-subtle text-danger'
                            ];
                            $cls = $status_class[$s['status']] ?? 'bg-secondary-subtle';
                        ?>
                        <span class="badge <?php echo $cls; ?> rounded-pill px-3"><?php echo $s['status']; ?></span>
                    </td>
                    <td data-label="Aksi" class="px-3 text-end">
                        <div class="btn-group">
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
                                    onclick="showConfirmModal('Hapus data siswa ini?', function() { document.getElementById('deleteForm-<?= $s['id'] ?>').submit(); })" 
                                    title="Hapus">
                                    <i class="fas fa-trash text-danger"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#siswaTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        },
        pageLength: 10,
        responsive: true,
        dom: '<"d-flex justify-content-between align-items-center mb-3"lf>rt<"d-flex justify-content-between align-items-center mt-3"ip>'
    });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>