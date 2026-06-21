<?php
// emadrasah/modules/tasks/history.php
$page_title = 'Riwayat Tugas';
include __DIR__ . '/../../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil info tugas
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
$stmt->execute([$id]);
$task = $stmt->fetch();

if (!$task) {
    header('Location: index.php');
    exit;
}

// Ambil log riwayat
$stmt_logs = $pdo->prepare("
    SELECT tl.*, u.name as user_name 
    FROM task_logs tl 
    LEFT JOIN users u ON tl.user_id = u.id 
    WHERE tl.task_id = ? 
    ORDER BY tl.created_at DESC
");
$stmt_logs->execute([$id]);
$logs = $stmt_logs->fetchAll();
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title"><i class="fas fa-history me-2 text-primary"></i>Riwayat Progres Tugas</h2>
        <p class="page-subtitle">Tugas: <strong><?php echo $task['judul']; ?></strong></p>
    </div>
    <div>
        <a href="edit.php?id=<?php echo $id; ?>" class="btn btn-light border">
            <i class="fas fa-arrow-left me-2"></i>Kembali ke Kelola
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm p-4 fade-in">
    <div class="timeline">
        <?php if (empty($logs)): ?>
            <div class="text-center py-5 text-muted">Belum ada riwayat aktivitas untuk tugas ini.</div>
        <?php else: ?>
            <?php foreach ($logs as $log): ?>
                <div class="d-flex gap-3 mb-4">
                    <div class="flex-shrink-0">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="fas fa-check-double small"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 border-start ps-3 pb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h6 class="fw-bold mb-0 text-dark"><?php echo $log['action']; ?></h6>
                            <span class="small text-muted"><?php echo date('d M Y, H:i', strtotime($log['created_at'])); ?></span>
                        </div>
                        <p class="text-muted small mb-1"><?php echo $log['keterangan']; ?></p>
                        <div class="small fw-bold text-primary">Oleh: <?php echo $log['user_name'] ?: 'System'; ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style>
    .timeline .flex-grow-1:last-child {
        border-start: none !important;
    }
</style>

<?php include __DIR__ . '/../../includes/footer.php'; ?>