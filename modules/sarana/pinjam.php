<?php
// emadrasah/modules/sarana/pinjam.php
$page_title = 'Peminjaman Sarana';
include __DIR__ . '/../../includes/header.php';

$sarana_id = isset($_GET['sarana_id']) ? (int)$_GET['sarana_id'] : 0;

// Ambil info sarana
$stmt = $pdo->prepare("SELECT * FROM sarana_prasarana WHERE id = ?");
$stmt->execute([$sarana_id]);
$sarana = $stmt->fetch();

if (!$sarana) {
    header('Location: index.php');
    exit;
}

// Ambil riwayat peminjaman untuk alat ini
$stmt_history = $pdo->prepare("SELECT * FROM peminjaman_sarana WHERE sarana_id = ? ORDER BY tanggal_pinjam DESC");
$stmt_history->execute([$sarana_id]);
$history = $stmt_history->fetchAll();
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title"><i class="fas fa-hand-holding me-2 text-info"></i>Peminjaman Barang</h2>
        <p class="page-subtitle">Aset: <strong><?php echo $sarana['nama_sarana']; ?></strong> (Tersedia: <span class="badge bg-info"><?php echo $sarana['stok_tersedia']; ?></span> dari <?php echo $sarana['jumlah']; ?>)</p>
    </div>
    <div>
        <a href="index.php" class="btn btn-light border">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<div class="row g-4 fade-in">
    <div class="col-xl-4">
        <div class="card border-0 shadow-sm p-4 h-100">
            <h5 class="fw-bold mb-4">Form Pinjam Baru</h5>
            <form action="pinjam_store.php" method="POST">
        <?php echo csrf_input(); ?>
                <input type="hidden" name="sarana_id" value="<?php echo $sarana_id; ?>">
                
                <div class="mb-3">
                    <label class="form-label small fw-bold">Nama Peminjam</label>
                    <input type="text" name="peminjam" class="form-control" placeholder="Nama lengkap..." required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Tipe Peminjam</label>
                    <select name="tipe_peminjam" class="form-select" required>
                        <option value="Guru">Guru / Staff</option>
                        <option value="Siswa">Siswa</option>
                        <option value="Luar">Pihak Luar</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Tanggal Pinjam</label>
                    <input type="date" name="tanggal_pinjam" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <div class="d-grid mt-4">
                    <?php if ($sarana['stok_tersedia'] > 0): ?>
                        <button type="submit" class="btn btn-info text-white fw-bold">
                            <i class="fas fa-save me-2"></i>Catat Peminjaman
                        </button>
                    <?php else: ?>
                        <button type="button" class="btn btn-secondary fw-bold" disabled>
                            <i class="fas fa-times-circle me-2"></i>Stok Habis
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="card border-0 shadow-sm overflow-hidden h-100">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">Riwayat Peminjaman Alat Ini</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3">Peminjam</th>
                            <th class="py-3">Tgl Pinjam</th>
                            <th class="py-3">Status</th>
                            <th class="px-4 py-3 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($history)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">Belum ada riwayat peminjaman.</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach($history as $h): ?>
                            <tr>
                                <td class="px-4">
                                    <div class="fw-bold"><?php echo $h['peminjam']; ?></div>
                                    <div class="small text-muted"><?php echo $h['tipe_peminjam']; ?></div>
                                </td>
                                <td><?php echo tgl_indo($h['tanggal_pinjam']); ?></td>
                                <td>
                                    <?php if ($h['status'] == 'dipinjam'): ?>
                                        <span class="badge bg-warning">Sedang Dipinjam</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Dikembalikan</span>
                                        <div class="small text-muted" style="font-size: 0.7rem;">Tgl: <?php echo tgl_indo($h['tanggal_kembali']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 text-end">
                                    <?php if ($h['status'] == 'dipinjam'): ?>
                                        <form action="pinjam_kembali.php" method="POST" class="d-inline" id="kembaliForm-<?= $h['id'] ?>">
                                            <?= csrf_input() ?>
                                            <input type="hidden" name="id" value="<?= $h['id'] ?>">
                                            <input type="hidden" name="sarana_id" value="<?= $sarana_id ?>">
                                            <button type="button" class="btn btn-sm btn-success"
                                                onclick="showConfirmModal('Proses pengembalian barang?', function() { document.getElementById('kembaliForm-<?= $h['id'] ?>').submit(); })">
                                                <i class="fas fa-undo me-1"></i> Kembali
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <i class="fas fa-check-circle text-success"></i>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>