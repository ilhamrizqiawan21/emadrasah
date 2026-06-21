<?php
// emadrasah/modules/sarana/index.php
$page_title = 'Sarana & Prasarana';
include __DIR__ . '/../../includes/header.php';

// Filter Kategori
$kategori_filter = isset($_GET['kategori_id']) ? $_GET['kategori_id'] : '';

$query_str = "SELECT s.*, k.nama_kategori 
              FROM sarana_prasarana s 
              JOIN kategori_sarana k ON s.kategori_id = k.id";

if ($kategori_filter) {
    $query_str .= " WHERE s.kategori_id = :kat";
}
$query_str .= " ORDER BY s.nama_sarana ASC";

$stmt = $pdo->prepare($query_str);
if ($kategori_filter) {
    $stmt->execute(['kat' => $kategori_filter]);
} else {
    $stmt->execute();
}
$assets = $stmt->fetchAll();

$kategori_list = $pdo->query("SELECT * FROM kategori_sarana ORDER BY nama_kategori ASC")->fetchAll();
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title"><i class="fas fa-boxes-stacked me-2 text-primary"></i>Sarana & Prasarana</h2>
        <p class="page-subtitle">Inventaris aset dan fasilitas Madrasah.</p>
    </div>
    <div>
        <a href="create.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Aset
        </a>
    </div>
</div>

<!-- Filter -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted">Filter Kategori</label>
                <select name="kategori_id" class="form-select">
                    <option value="">Semua Kategori</option>
                    <?php foreach($kategori_list as $kat): ?>
                    <option value="<?php echo $kat['id']; ?>" <?php echo $kategori_filter == $kat['id'] ? 'selected' : ''; ?>><?php echo $kat['nama_kategori']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-4 fade-in">
    <?php if (empty($assets)): ?>
    <div class="col-12 text-center py-5 text-muted bg-white rounded-4 border">
        <i class="fas fa-box-open fa-3x mb-3 opacity-25"></i>
        <p>Data aset tidak ditemukan.</p>
    </div>
    <?php else: ?>
        <?php foreach ($assets as $a): ?>
        <div class="col-sm-6 col-lg-4 col-xl-3">
            <div class="card border-0 shadow-sm h-100 overflow-hidden">
                <div class="bg-light d-flex align-items-center justify-content-center" style="height: 160px;">
                    <?php if($a['foto']): ?>
                        <img src="<?php echo base_url('uploads/sarana/' . $a['foto']); ?>" class="w-100 h-100 object-fit-cover">
                    <?php else: ?>
                        <i class="fas fa-tools fa-3x text-muted opacity-25"></i>
                    <?php endif; ?>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge bg-light text-primary border border-primary-subtle small"><?php echo $a['kode_sarana']; ?></span>
                        <?php 
                            $cond_cls = ['baik' => 'bg-success', 'rusak_ringan' => 'bg-warning', 'rusak_berat' => 'bg-danger', 'hilang' => 'bg-dark'];
                            $cond_text = ['baik' => 'Baik', 'rusak_ringan' => 'Rusak Ringan', 'rusak_berat' => 'Rusak Berat', 'hilang' => 'Hilang'];
                        ?>
                        <span class="badge <?php echo $cond_cls[$a['kondisi']]; ?> rounded-pill" style="font-size: 0.65rem;">
                            <?php echo $cond_text[$a['kondisi']]; ?>
                        </span>
                    </div>
                    <h6 class="fw-bold text-dark mb-1"><?php echo $a['nama_sarana']; ?></h6>
                    <p class="small text-muted mb-2"><i class="fas fa-map-marker-alt me-1"></i> <?php echo $a['lokasi_ruang'] ?: 'Tidak ada lokasi'; ?></p>
                    
                    <div class="d-flex flex-column gap-2 mt-3 pt-2 border-top">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small text-muted">Stok: <strong><?php echo $a['stok_tersedia']; ?></strong> / <?php echo $a['jumlah']; ?></span>
                            <div class="btn-group">
                                <a href="edit.php?id=<?php echo $a['id']; ?>" class="btn btn-xs btn-light border" title="Edit"><i class="fas fa-edit text-warning"></i></a>
                                <form action="delete.php" method="POST" class="d-inline" id="deleteForm-<?= $a['id'] ?>">
                                    <?= csrf_input() ?>
                                    <input type="hidden" name="id" value="<?= $a['id'] ?>">
                                    <button type="button" class="btn btn-xs btn-light border" 
                                        onclick="showConfirmModal('Hapus aset ini?', function() { document.getElementById('deleteForm-<?= $a['id'] ?>').submit(); })" 
                                        title="Hapus">
                                        <i class="fas fa-trash text-danger"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="d-grid gap-2 d-md-flex">
                            <a href="pinjam.php?sarana_id=<?php echo $a['id']; ?>" class="btn btn-xs btn-outline-info flex-fill" style="font-size: 0.7rem;">
                                <i class="fas fa-hand-holding me-1"></i> Pinjam
                            </a>
                            <a href="servis.php?sarana_id=<?php echo $a['id']; ?>" class="btn btn-xs btn-outline-warning flex-fill" style="font-size: 0.7rem;">
                                <i class="fas fa-wrench me-1"></i> Servis
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>