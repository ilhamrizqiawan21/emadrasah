<?php
// emadrasah/modules/tasks/show.php
$page_title = 'Detail Tugas';
include __DIR__ . '/../../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT t.*, u.name as assigned_name, c.name as created_name 
                       FROM tasks t 
                       LEFT JOIN users u ON t.assigned_to = u.id 
                       LEFT JOIN users c ON t.created_by = c.id 
                       WHERE t.id = ?");
$stmt->execute([$id]);
$task = $stmt->fetch();

if (!$task) {
    header('Location: index.php');
    exit;
}

$status_label = [
    'antrean' => 'warning',
    'proses' => 'info',
    'selesai' => 'success',
];
$priority_label = [
    'high' => 'danger',
    'sedang' => 'primary',
    'rendah' => 'secondary',
];
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title"><i class="fas fa-tasks me-2 text-primary"></i>Detail Tugas</h2>
        <p class="page-subtitle"><?php echo $task['judul']; ?></p>
    </div>
    <div>
        <a href="index.php" class="btn btn-light border">
            <i class="fas fa-arrow-left me-2"></i>Kembali ke Daftar
        </a>
    </div>
</div>

<div class="row g-4 fade-in">
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm p-4 mb-4">
            <div class="d-flex justify-content-between align-items-start mb-4">
                <div>
                    <span class="badge bg-<?php echo $status_label[$task['status']] ?? 'secondary'; ?> me-2"><?php echo ucfirst($task['status']); ?></span>
                    <span class="badge bg-<?php echo $priority_label[$task['prioritas']] ?? 'secondary'; ?>">Prioritas: <?php echo ucfirst($task['prioritas']); ?></span>
                </div>
                <span class="small text-muted">Deadline: <?php echo date('d M Y', strtotime($task['deadline'])); ?></span>
            </div>

            <h4 class="fw-bold mb-3"><?php echo $task['judul']; ?></h4>
            <p class="text-muted mb-4"><?php echo nl2br($task['deskripsi']); ?></p>

            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <div class="card border-0 bg-light p-3">
                        <div class="small text-muted">Status</div>
                        <div class="fw-bold"><?php echo ucfirst($task['status']); ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 bg-light p-3">
                        <div class="small text-muted">Penanggung Jawab</div>
                        <div class="fw-bold"><?php echo $task['assigned_name'] ?: 'Unassigned'; ?></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 bg-light p-3">
                        <div class="small text-muted">Dibuat Oleh</div>
                        <div class="fw-bold"><?php echo $task['created_name'] ?: 'System'; ?></div>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <h6 class="fw-bold">Progress</h6>
                <div class="d-flex align-items-center gap-3">
                    <div class="progress flex-grow-1" style="height: 10px;">
                        <div class="progress-bar bg-primary" style="width: <?php echo $task['progress_persen']; ?>%;"></div>
                    </div>
                    <span class="fw-bold"><?php echo $task['progress_persen']; ?>%</span>
                </div>
            </div>

            <?php if ($task['attachment']): ?>
            <div class="mb-4">
                <h6 class="fw-bold">Lampiran</h6>
                <a href="<?php echo base_url('uploads/tasks/' . $task['attachment']); ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                    <i class="fas fa-file-download me-1"></i> <?php echo $task['attachment']; ?>
                </a>
            </div>
            <?php endif; ?>

            <div class="d-flex gap-2">
                <a href="edit.php?id=<?php echo $task['id']; ?>" class="btn btn-primary">
                    <i class="fas fa-edit me-1"></i> Ubah Tugas
                </a>
                <a href="history.php?id=<?php echo $task['id']; ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-history me-1"></i> Lihat Riwayat
                </a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
