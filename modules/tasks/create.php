<?php
// emadrasah/modules/tasks/create.php
$page_title = 'Buat Tugas Baru';
include __DIR__ . '/../../includes/header.php';

// Ambil data users (Staf/Operator) untuk penugasan
$users = $pdo->query("SELECT id, name FROM users ORDER BY name ASC")->fetchAll();
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title">Buat Tugas Baru</h2>
        <p class="page-subtitle">Tugaskan pekerjaan kepada staf Tata Usaha.</p>
    </div>
    <div>
        <a href="index.php" class="btn btn-light border">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm p-4 fade-in" style="max-width: 800px;">
    <form action="store.php" method="POST" enctype="multipart/form-data">
        <?php echo csrf_input(); ?>
        <div class="row g-3">
            <div class="col-md-12">
                <label class="form-label small fw-bold">Judul Tugas <span class="text-danger">*</span></label>
                <input type="text" name="judul" class="form-control" required placeholder="Contoh: Input Data Siswa Baru">
            </div>
            <div class="col-md-12">
                <label class="form-label small fw-bold">Deskripsi Pekerjaan</label>
                <textarea name="deskripsi" class="form-control" rows="4" placeholder="Jelaskan detail tugas..."></textarea>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Tugaskan Kepada</label>
                <select name="assigned_to" class="form-select">
                    <option value="">-- Pilih Staf --</option>
                    <?php foreach($users as $u): ?>
                    <option value="<?php echo $u['id']; ?>"><?php echo $u['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Kategori</label>
                <input type="text" name="kategori" class="form-control" placeholder="Contoh: Kesiswaan, Keuangan">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Prioritas</label>
                <select name="prioritas" class="form-select">
                    <option value="rendah">Rendah</option>
                    <option value="sedang" selected>Sedang</option>
                    <option value="high">Tinggi</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Deadline <span class="text-danger">*</span></label>
                <input type="date" name="deadline" class="form-control" required value="<?php echo date('Y-m-d', strtotime('+3 days')); ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Lampiran Tugas</label>
                <input type="file" name="attachment" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx,.zip">
                <div class="form-text">Opsional. Unggah file pendukung tugas.</div>
            </div>
        </div>

        <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary px-5 fw-bold">Buat Tugas</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>