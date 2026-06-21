<?php
// emadrasah/modules/jam_pelajaran/edit.php
$page_title = 'Edit Sesi';
include __DIR__ . '/../../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM jam_pelajaran WHERE id = ?");
$stmt->execute([$id]);
$j = $stmt->fetch();

if (!$j) {
    header('Location: index.php');
    exit;
}
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title">Edit Jam Pelajaran</h2>
        <p class="page-subtitle">Memperbarui detail jam pelajaran.</p>
    </div>
    <div>
        <a href="<?php echo base_url('modules/jam_pelajaran/index.php'); ?>" class="btn btn-light border">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm p-4 fade-in" style="max-width: 600px;">
    <form action="<?php echo base_url('modules/jam_pelajaran/update.php'); ?>" method="POST">
        <?php echo csrf_input(); ?>
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <div class="mb-3">
            <label class="form-label small fw-bold">Hari <span class="text-danger">*</span></label>
            <select name="hari" class="form-select" required>
                <?php $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu']; ?>
                <?php foreach ($days as $day): ?>
                    <option value="<?php echo $day; ?>" <?php echo $j['hari'] == $day ? 'selected' : ''; ?>><?php echo $day; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label small fw-bold">Jam Ke- <span class="text-danger">*</span></label>
            <input type="number" name="sesi_ke" class="form-control" required min="1" value="<?php echo $j['sesi_ke']; ?>">
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label small fw-bold">Jam Mulai <span class="text-danger">*</span></label>
                <input type="time" name="jam_mulai" class="form-control" required value="<?php echo substr($j['jam_mulai'], 0, 5); ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Jam Selesai <span class="text-danger">*</span></label>
                <input type="time" name="jam_selesai" class="form-control" required value="<?php echo substr($j['jam_selesai'], 0, 5); ?>">
            </div>
        </div>

        <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary px-5 fw-bold">Perbarui Jam Pelajaran</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
