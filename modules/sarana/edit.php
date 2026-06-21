<?php
// emadrasah/modules/sarana/edit.php
$page_title = 'Edit Aset Sarana';
include __DIR__ . '/../../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM sarana_prasarana WHERE id = ?");
$stmt->execute([$id]);
$a = $stmt->fetch();

if (!$a) {
    header('Location: index.php');
    exit;
}

$kategori_list = $pdo->query("SELECT * FROM kategori_sarana ORDER BY nama_kategori ASC")->fetchAll();
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title">Edit Aset</h2>
        <p class="page-subtitle">Memperbarui informasi: <strong><?php echo $a['nama_sarana']; ?></strong></p>
    </div>
    <div>
        <a href="index.php" class="btn btn-light border">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm p-4 fade-in" style="max-width: 800px;">
    <form action="update.php" method="POST" enctype="multipart/form-data">
        <?php echo csrf_input(); ?>
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label small fw-bold">Kode Aset</label>
                <input type="text" name="kode_sarana" class="form-control" required value="<?php echo $a['kode_sarana']; ?>">
            </div>
            <div class="col-md-8">
                <label class="form-label small fw-bold">Nama Aset/Barang</label>
                <input type="text" name="nama_sarana" class="form-control" required value="<?php echo $a['nama_sarana']; ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Kategori</label>
                <select name="kategori_id" class="form-select" required>
                    <?php foreach($kategori_list as $kat): ?>
                    <option value="<?php echo $kat['id']; ?>" <?php echo $a['kategori_id'] == $kat['id'] ? 'selected' : ''; ?>><?php echo $kat['nama_kategori']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Jumlah</label>
                <input type="number" name="jumlah" class="form-control" value="<?php echo $a['jumlah']; ?>" min="0">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Kondisi</label>
                <select name="kondisi" class="form-select">
                    <option value="baik" <?php echo $a['kondisi'] == 'baik' ? 'selected' : ''; ?>>Baik</option>
                    <option value="rusak_ringan" <?php echo $a['kondisi'] == 'rusak_ringan' ? 'selected' : ''; ?>>Rusak Ringan</option>
                    <option value="rusak_berat" <?php echo $a['kondisi'] == 'rusak_berat' ? 'selected' : ''; ?>>Rusak Berat</option>
                    <option value="hilang" <?php echo $a['kondisi'] == 'hilang' ? 'selected' : ''; ?>>Hilang</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Lokasi Ruang</label>
                <input type="text" name="lokasi_ruang" class="form-control" value="<?php echo $a['lokasi_ruang']; ?>">
            </div>
            <div class="col-md-6">
                <label class="form-label small fw-bold">Tahun Pengadaan</label>
                <input type="number" name="tahun_pengadaan" class="form-control" value="<?php echo $a['tahun_pengadaan']; ?>">
            </div>
            <div class="col-md-12">
                <label class="form-label small fw-bold">Spesifikasi / Keterangan</label>
                <textarea name="spesifikasi" class="form-control" rows="3"><?php echo $a['spesifikasi']; ?></textarea>
            </div>
            <div class="col-md-12">
                <label class="form-label small fw-bold">Ganti Foto (Kosongkan jika tidak ingin ganti)</label>
                <input type="file" name="foto" class="form-control" accept="image/*">
            </div>
        </div>

        <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary px-5 fw-bold">Perbarui Aset</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>