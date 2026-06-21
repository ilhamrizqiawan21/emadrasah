<?php
// emadrasah/modules/tasks/index.php
$page_title = 'Daftar Tugas TU';
include __DIR__ . '/../../includes/header.php';

// Filter Status, Prioritas, dan Pencarian
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$prioritas_filter = isset($_GET['prioritas']) ? $_GET['prioritas'] : '';
$search_query = isset($_GET['q']) ? input_safe($_GET['q']) : '';

$query_str = "SELECT t.*, u.name as assigned_name 
              FROM tasks t 
              LEFT JOIN users u ON t.assigned_to = u.id 
              WHERE 1=1";

$params = [];
if ($status_filter) {
    $query_str .= " AND t.status = :status";
    $params['status'] = $status_filter;
}
if ($prioritas_filter) {
    $query_str .= " AND t.prioritas = :prioritas";
    $params['prioritas'] = $prioritas_filter;
}
if ($search_query) {
    $query_str .= " AND (t.judul LIKE :q OR t.deskripsi LIKE :q OR t.kategori LIKE :q)";
    $params['q'] = "%{$search_query}%";
}

$query_str .= " ORDER BY CASE WHEN prioritas = 'high' THEN 1 WHEN prioritas = 'sedang' THEN 2 ELSE 3 END, deadline ASC";

$stmt = $pdo->prepare($query_str);
$stmt->execute($params);
$tasks = $stmt->fetchAll();
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title"><i class="fas fa-tasks me-2 text-primary"></i>Manajemen Tugas TU</h2>
        <p class="page-subtitle">Monitoring penyelesaian tugas staf Tata Usaha.</p>
    </div>
    <div>
        <a href="create.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Buat Tugas Baru
        </a>
    </div>
</div>

<!-- Quick Filter -->
<div class="d-flex gap-2 mb-4 fade-in">
    <a href="index.php" class="btn btn-sm <?php echo !$status_filter ? 'btn-primary' : 'btn-light border'; ?> rounded-pill px-3">Semua</a>
    <a href="index.php?status=antrean" class="btn btn-sm <?php echo $status_filter == 'antrean' ? 'btn-warning text-white' : 'btn-light border'; ?> rounded-pill px-3">Antrean</a>
    <a href="index.php?status=proses" class="btn btn-sm <?php echo $status_filter == 'proses' ? 'btn-info text-white' : 'btn-light border'; ?> rounded-pill px-3">Proses</a>
    <a href="index.php?status=selesai" class="btn btn-sm <?php echo $status_filter == 'selesai' ? 'btn-success' : 'btn-light border'; ?> rounded-pill px-3">Selesai</a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label small fw-bold">Cari Tugas</label>
                <input type="text" name="q" class="form-control" placeholder="Judul, deskripsi, kategori" value="<?php echo $search_query; ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Filter Prioritas</label>
                <select name="prioritas" class="form-select">
                    <option value="">Semua Prioritas</option>
                    <option value="high" <?php echo $prioritas_filter == 'high' ? 'selected' : ''; ?>>Tinggi</option>
                    <option value="sedang" <?php echo $prioritas_filter == 'sedang' ? 'selected' : ''; ?>>Sedang</option>
                    <option value="rendah" <?php echo $prioritas_filter == 'rendah' ? 'selected' : ''; ?>>Rendah</option>
                </select>
            </div>
            <div class="col-md-3 text-end">
                <label class="form-label small fw-bold invisible">Aksi</label>
                <button type="submit" class="btn btn-outline-primary w-100">Terapkan</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-4 fade-in">
    <?php if (empty($tasks)): ?>
    <div class="col-12 text-center py-5 text-muted bg-white rounded-4 border">
        <i class="fas fa-clipboard-list fa-3x mb-3 opacity-25"></i>
        <p>Belum ada tugas dalam kategori ini.</p>
    </div>
    <?php else: ?>
        <?php foreach ($tasks as $t): ?>
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100 position-relative overflow-hidden">
                <?php 
                    $prio_cls = ['high' => 'bg-danger', 'sedang' => 'bg-warning', 'rendah' => 'bg-info'];
                    $status_cls = ['antrean' => 'secondary', 'proses' => 'info', 'selesai' => 'success'];
                ?>
                <div class="position-absolute top-0 start-0 w-100" style="height: 4px; background: <?php echo $t['prioritas'] == 'high' ? '#ef4444' : ($t['prioritas'] == 'sedang' ? '#f59e0b' : '#3b82f6'); ?>"></div>
                
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="badge bg-light text-dark border small"><?php echo ucfirst($t['kategori'] ?: 'Umum'); ?></span>
                        <span class="badge bg-<?php echo $status_cls[$t['status']]; ?> rounded-pill"><?php echo ucfirst($t['status']); ?></span>
                    </div>
                    
                    <h6 class="fw-bold text-dark mb-2"><?php echo $t['judul']; ?></h6>
                    <p class="small text-muted mb-4 text-truncate-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                        <?php echo $t['deskripsi']; ?>
                    </p>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between small mb-1">
                            <span class="text-muted">Progress</span>
                            <span class="fw-bold"><?php echo $t['progress_persen']; ?>%</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-primary" style="width: <?php echo $t['progress_persen']; ?>%"></div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top">
                        <div class="d-flex align-items-center">
                            <div class="em-user-avatar bg-light text-primary me-2" style="width: 28px; height: 28px; font-size: 0.7rem;">
                                <span><?php echo strtoupper(substr($t['assigned_name'] ?: '?', 0, 1)); ?></span>
                            </div>
                            <span class="small fw-bold text-dark"><?php echo $t['assigned_name'] ?: 'Unassigned'; ?></span>
                        </div>
                        <div class="small text-<?php echo (strtotime($t['deadline']) < time() && $t['status'] != 'selesai') ? 'danger' : 'muted'; ?> fw-bold">
                            <i class="fas fa-calendar-day me-1"></i> <?php echo date('d M', strtotime($t['deadline'])); ?>
                        </div>
                    </div>

                    <div class="mt-3 d-grid gap-2">
                        <a href="show.php?id=<?php echo $t['id']; ?>" class="btn btn-sm btn-outline-secondary fw-bold">
                            <i class="fas fa-eye me-1"></i> Detail Tugas
                        </a>
                        <a href="edit.php?id=<?php echo $t['id']; ?>" class="btn btn-sm btn-outline-primary fw-bold">
                            <i class="fas fa-cog me-1"></i> Kelola Tugas
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>