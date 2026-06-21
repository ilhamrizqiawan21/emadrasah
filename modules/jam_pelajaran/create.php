<?php
// emadrasah/modules/jam_pelajaran/create.php
$page_title = 'Tambah Sesi Baru';
include __DIR__ . '/../../includes/header.php';
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title">Tambah Jam Pelajaran</h2>
        <p class="page-subtitle">Input detail waktu untuk jam pelajaran baru.</p>
    </div>
    <div>
        <a href="<?php echo base_url('modules/jam_pelajaran/index.php'); ?>" class="btn btn-light border">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm p-4 fade-in" style="max-width: 600px;">
    <form action="store.php" method="POST">
        <?php echo csrf_input(); ?>
        <div class="mb-3">
            <label class="form-label small fw-bold">Hari <span class="text-danger">*</span></label>
            <select name="hari" class="form-select" required>
                <option value="Senin">Senin</option>
                <option value="Selasa">Selasa</option>
                <option value="Rabu">Rabu</option>
                <option value="Kamis">Kamis</option>
                <option value="Jumat">Jumat</option>
                <option value="Sabtu">Sabtu</option>
                <option value="Minggu">Minggu</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label small fw-bold">Sesi Ke- <span class="text-danger">*</span></label>
            <input type="number" name="sesi_ke" class="form-control" required min="1" placeholder="Contoh: 1">
        </div>
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label small fw-bold">Jam Mulai <span class="text-danger">*</span></label>
                <input type="time" name="jam_mulai" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Jam Selesai <span class="text-danger">*</span></label>
                <input type="time" name="jam_selesai" class="form-control" required>
            </div>
        </div>

        <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary px-5 fw-bold">Simpan Sesi</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
