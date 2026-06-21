<?php
// emadrasah/modules/raport/index.php
$page_title = 'Manajemen Nilai Raport';
include __DIR__ . '/../../includes/header.php';

// Fitur Pencarian
$search = isset($_GET['search']) ? input_safe($_GET['search']) : '';

// Query Dasar dengan Join Kelas
$query_str = "SELECT s.*, k.nama_kelas 
              FROM siswa s 
              LEFT JOIN kelas k ON s.kelas_id = k.id";

if ($search) {
    $query_str .= " WHERE s.nama_lengkap LIKE :search OR s.nis LIKE :search";
}

$query_str .= " ORDER BY k.nama_kelas ASC, s.nama_lengkap ASC";

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
        <h2 class="page-title">Input Nilai Raport</h2>
        <p class="page-subtitle">Pilih siswa untuk mengelola nilai capaian hasil belajar.</p>
    </div>
</div>

<!-- Search -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Cari Nama atau NIS..." value="<?php echo $search; ?>">
                    <button type="submit" class="btn btn-primary px-4">Cari Siswa</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="row g-4 fade-in">
    <?php if (empty($siswa_list)): ?>
    <div class="col-12">
        <div class="card border-0 shadow-sm p-5 text-center">
            <i class="fas fa-search fa-3x mb-3 text-muted opacity-25"></i>
            <p class="text-muted">Siswa tidak ditemukan atau belum ada data siswa.</p>
        </div>
    </div>
    <?php else: ?>
        <?php foreach ($siswa_list as $s): ?>
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100 overflow-hidden">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-3">
                        <div class="em-user-avatar bg-success-subtle text-success fw-bold me-3" style="width: 50px; height: 50px; font-size: 1.2rem;">
                            <span><?php echo strtoupper(substr($s['nama_lengkap'], 0, 1)); ?></span>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark"><?php echo $s['nama_lengkap']; ?></h6>
                            <small class="text-muted">NIS: <?php echo $s['nis']; ?></small>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <span class="badge bg-light text-primary border border-primary-subtle">
                            <i class="fas fa-door-open me-1"></i> Kelas <?php echo $s['nama_kelas'] ?: '-'; ?>
                        </span>
                        <span class="small text-muted">Status: <?php echo $s['status']; ?></span>
                    </div>

                    <div class="d-grid">
                        <a href="<?php echo base_url('modules/raport/manage.php?id=' . $s['id']); ?>" class="btn btn-outline-primary btn-sm fw-bold">
                            <i class="fas fa-edit me-2"></i>Kelola Nilai & Raport
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>