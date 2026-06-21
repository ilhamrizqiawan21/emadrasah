<?php
// emadrasah/modules/kelas/edit.php
$page_title = 'Edit Data Kelas';
include __DIR__ . '/../../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM kelas WHERE id = ?");
$stmt->execute([$id]);
$k = $stmt->fetch();

if (!$k) {
    set_flash('danger', 'Data kelas tidak ditemukan.');
    header('Location: index.php');
    exit;
}

// Ambil data guru untuk Wali Kelas
$guru_list = $pdo->query("SELECT id, nama FROM gurus ORDER BY nama ASC")->fetchAll();
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title">Edit Data Kelas</h2>
        <p class="page-subtitle">Memperbarui informasi: <strong><?php echo $k['nama_kelas']; ?></strong></p>
    </div>
    <div>
        <a href="index.php" class="btn btn-light border">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm p-4 fade-in">
    <form action="update.php" method="POST">
        <?php echo csrf_input(); ?>
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Tingkat <span class="text-danger">*</span></label>
                <select name="tingkat" class="form-select" required>
                    <option value="7" <?php echo $k['tingkat'] == 7 ? 'selected' : ''; ?>>7</option>
                    <option value="8" <?php echo $k['tingkat'] == 8 ? 'selected' : ''; ?>>8</option>
                    <option value="9" <?php echo $k['tingkat'] == 9 ? 'selected' : ''; ?>>9</option>
                </select>
            </div>
            <div class="col-md-8">
                <label class="form-label">Nama Kelas <span class="text-danger">*</span></label>
                <input type="text" name="nama_kelas" class="form-control" required value="<?php echo $k['nama_kelas']; ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label">Wali Kelas</label>
                <select name="guru_pembimbing_id" class="form-select">
                    <option value="">-- Pilih Wali Kelas --</option>
                    <?php foreach($guru_list as $g): ?>
                    <option value="<?php echo $g['id']; ?>" <?php echo $k['guru_pembimbing_id'] == $g['id'] ? 'selected' : ''; ?>><?php echo $g['nama']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Kapasitas</label>
                <input type="number" name="kapasitas" class="form-control" value="<?php echo $k['kapasitas']; ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Ruangan</label>
                <input type="text" name="ruangan" class="form-control" value="<?php echo $k['ruangan']; ?>">
            </div>
        </div>

        <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary px-5 fw-bold">Perbarui Data Kelas</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
