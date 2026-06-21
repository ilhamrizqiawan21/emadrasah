<?php
// emadrasah/modules/arsip/create.php
$page_title = 'Upload Arsip Akademik';
include __DIR__ . '/../../includes/header.php';

$tp_list = $pdo->query("SELECT * FROM tahun_pelajaran ORDER BY kode DESC")->fetchAll();
$kelas_list = $pdo->query("SELECT * FROM kelas ORDER BY nama_kelas ASC")->fetchAll();
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title">Upload Arsip Baru</h2>
        <p class="page-subtitle">Simpan dokumen administratif ke dalam sistem.</p>
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
                <label class="form-label small fw-bold">Nama Arsip <span class="text-danger">*</span></label>
                <input type="text" name="nama_arsip" class="form-control" required placeholder="Contoh: Leger Nilai Kelas 7A Ganjil">
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Tahun Pelajaran <span class="text-danger">*</span></label>
                <select name="tahun_pelajaran_id" class="form-select" required>
                    <?php foreach($tp_list as $tp): ?>
                    <option value="<?php echo $tp['id']; ?>" <?php echo $tp['is_aktif'] ? 'selected' : ''; ?>><?php echo $tp['kode']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Kelas <span class="text-danger">*</span></label>
                <select name="kelas_id" class="form-select" required>
                    <?php foreach($kelas_list as $kls): ?>
                    <option value="<?php echo $kls['id']; ?>"><?php echo $kls['nama_kelas']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold">Semester <span class="text-danger">*</span></label>
                <select name="semester" class="form-select" required>
                    <option value="1">1 (Ganjil)</option>
                    <option value="2">2 (Genap)</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Tipe Arsip</label>
                <select name="tipe" class="form-select">
                    <option value="Leger">Leger</option>
                    <option value="RDM">RDM</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Pilih File (PDF/Excel/ZIP) <span class="text-danger">*</span></label>
                <input type="file" name="file_arsip" class="form-control" required accept=".pdf,.xls,.xlsx,.zip">
            </div>
        </div>

        <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary px-5 fw-bold">Upload Arsip</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>