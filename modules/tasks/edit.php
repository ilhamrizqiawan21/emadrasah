<?php
// emadrasah/modules/tasks/edit.php
$page_title = 'Kelola Tugas';
include __DIR__ . '/../../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM tasks WHERE id = ?");
$stmt->execute([$id]);
$t = $stmt->fetch();

if (!$t) {
    header('Location: index.php');
    exit;
}

$users = $pdo->query("SELECT id, name FROM users ORDER BY name ASC")->fetchAll();
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title">Kelola Tugas</h2>
        <p class="page-subtitle">Update status dan detail pekerjaan: <strong><?php echo $t['judul']; ?></strong></p>
    </div>
    <div>
        <a href="index.php" class="btn btn-light border">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<div class="row g-4 fade-in">
    <div class="col-xl-8">
        <div class="card border-0 shadow-sm p-4 mb-4">
            <form action="update.php" method="POST" enctype="multipart/form-data">
        <?php echo csrf_input(); ?>
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label small fw-bold">Judul Tugas</label>
                        <input type="text" name="judul" class="form-control" required value="<?php echo $t['judul']; ?>">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label small fw-bold">Deskripsi Pekerjaan</label>
                        <textarea name="deskripsi" class="form-control" rows="4"><?php echo $t['deskripsi']; ?></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Tugaskan Kepada</label>
                        <select name="assigned_to" class="form-select">
                            <option value="">-- Pilih Staf --</option>
                            <?php foreach($users as $u): ?>
                            <option value="<?php echo $u['id']; ?>" <?php echo $t['assigned_to'] == $u['id'] ? 'selected' : ''; ?>><?php echo $u['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Kategori</label>
                        <input type="text" name="kategori" class="form-control" value="<?php echo $t['kategori']; ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Prioritas</label>
                        <select name="prioritas" class="form-select">
                            <option value="rendah" <?php echo $t['prioritas'] == 'rendah' ? 'selected' : ''; ?>>Rendah</option>
                            <option value="sedang" <?php echo $t['prioritas'] == 'sedang' ? 'selected' : ''; ?>>Sedang</option>
                            <option value="high" <?php echo $t['prioritas'] == 'high' ? 'selected' : ''; ?>>Tinggi (High)</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold">Deadline</label>
                        <input type="date" name="deadline" class="form-control" required value="<?php echo $t['deadline']; ?>">
                    </div>
                    <div class="col-md-12">
                        <?php if ($t['attachment']): ?>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">File Saat Ini</label>
                            <div><a href="<?php echo base_url('uploads/tasks/' . $t['attachment']); ?>" target="_blank"><?php echo $t['attachment']; ?></a></div>
                        </div>
                        <?php endif; ?>
                        <label class="form-label small fw-bold">Ubah Lampiran</label>
                        <input type="file" name="attachment" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.zip">
                        <div class="form-text">Unggah file baru untuk mengganti lampiran saat ini.</div>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <form action="delete.php" method="POST" class="d-inline" id="deleteForm-<?= $id ?>">
                        <?= csrf_input() ?>
                        <input type="hidden" name="id" value="<?= $id ?>">
                        <button type="button" class="btn btn-outline-danger me-2" 
                            onclick="showConfirmModal('Hapus tugas ini?', function() { document.getElementById('deleteForm-<?= $id ?>').submit(); })">
                            Hapus Tugas
                        </button>
                    </form>
                    <button type="submit" class="btn btn-primary px-5 fw-bold">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card border-0 shadow-sm p-4 bg-light">
            <h5 class="fw-bold mb-4">Update Progress</h5>
            <form action="update_progress.php" method="POST">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                
                <div class="mb-4">
                    <label class="form-label small fw-bold">Status Saat Ini</label>
                    <select name="status" class="form-select fw-bold">
                        <option value="antrean" <?php echo $t['status'] == 'antrean' ? 'selected' : ''; ?>>⚪ Antrean</option>
                        <option value="proses" <?php echo $t['status'] == 'proses' ? 'selected' : ''; ?>>🔵 Proses</option>
                        <option value="selesai" <?php echo $t['status'] == 'selesai' ? 'selected' : ''; ?>>🟢 Selesai</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold">Persentase Selesai (<?php echo $t['progress_persen']; ?>%)</label>
                    <input type="range" name="progress_persen" class="form-range" min="0" max="100" step="5" value="<?php echo $t['progress_persen']; ?>">
                    <div class="d-flex justify-content-between small text-muted">
                        <span>0%</span>
                        <span>50%</span>
                        <span>100%</span>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-dark fw-bold">Update Progress</button>
                    <a href="history.php?id=<?php echo $id; ?>" class="btn btn-outline-primary btn-sm fw-bold">
                        <i class="fas fa-history me-1"></i> Lihat Riwayat Progres
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>