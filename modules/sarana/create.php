<?php
// emadrasah/modules/sarana/create.php
$page_title = 'Tambah Aset Sarana';
include __DIR__ . '/../../includes/header.php';

$kategori_list = $pdo->query("SELECT * FROM kategori_sarana ORDER BY nama_kategori ASC")->fetchAll();
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title">Tambah Aset</h2>
        <p class="page-subtitle">Input data barang atau fasilitas baru.</p>
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
            <div class="col-md-4">
                <label class="form-label small fw-bold">Kode Aset <span class="text-danger">*</span></label>
                <input type="text" name="kode_sarana" class="form-control" required placeholder="Contoh: ELK-001">
            </div>
            <div class="col-md-8">
                <label class="form-label small fw-bold">Nama Aset/Barang <span class="text-danger">*</span></label>
                <input type="text" name="nama_sarana" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Kategori</label>
                <select name="kategori_id" class="form-select" required>
                    <?php foreach($kategori_list as $kat): ?>
                    <option value="<?php echo $kat['id']; ?>"><?php echo $kat['nama_kategori']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Jumlah</label>
                <input type="number" name="jumlah" class="form-control" value="1" min="1">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Kondisi</label>
                <select name="kondisi" class="form-select">
                    <option value="baik">Baik</option>
                    <option value="rusak_ringan">Rusak Ringan</option>
                    <option value="rusak_berat">Rusak Berat</option>
                    <option value="hilang">Hilang</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Lokasi Ruang</label>
                <input type="text" name="lokasi_ruang" class="form-control" placeholder="Contoh: Lab Komputer">
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Tahun Pengadaan</label>
                <input type="number" name="tahun_pengadaan" class="form-control" value="<?php echo date('Y'); ?>">
            </div>
            <div class="col-md-12">
                <label class="form-label small fw-bold">Spesifikasi / Keterangan</label>
                <textarea name="spesifikasi" class="form-control" rows="3"></textarea>
            </div>
            <div class="col-md-12">
                <label class="form-label small fw-bold">Foto Barang</label>
                <input type="file" name="foto" class="form-control" accept="image/*">
            </div>
        </div>

        <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary px-5 fw-bold">Simpan Aset</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>