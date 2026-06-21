<?php
// emadrasah/modules/sarana/servis.php
$page_title = 'Pemeliharaan Sarana';
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

// Ambil riwayat pemeliharaan
$stmt_history = $pdo->prepare("SELECT * FROM pemeliharaan_sarana WHERE sarana_id = ? ORDER BY tanggal_pemeliharaan DESC");
$stmt_history->execute([$sarana_id]);
$history = $stmt_history->fetchAll();
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title"><i class="fas fa-wrench me-2 text-warning"></i>Pemeliharaan & Servis</h2>
        <p class="page-subtitle">Aset: <strong><?php echo $sarana['nama_sarana']; ?></strong></p>
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
            <h5 class="fw-bold mb-4">Catat Pemeliharaan</h5>
            <form action="servis_store.php" method="POST">
        <?php echo csrf_input(); ?>
                <input type="hidden" name="sarana_id" value="<?php echo $sarana_id; ?>">
                
                <div class="mb-3">
                    <label class="form-label small fw-bold">Tanggal Pemeliharaan</label>
                    <input type="date" name="tanggal_pemeliharaan" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Teknisi / Penanggung Jawab</label>
                    <input type="text" name="teknisi" class="form-control" placeholder="Nama teknisi..." required>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold">Keterangan / Perbaikan</label>
                    <textarea name="keterangan" class="form-control" rows="4" placeholder="Contoh: Ganti aki, service berkala..." required></textarea>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-warning text-dark fw-bold">
                        <i class="fas fa-save me-2"></i>Simpan Catatan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="col-xl-8">
        <div class="card border-0 shadow-sm overflow-hidden h-100">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-bold">Log Pemeliharaan Berkala</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3" style="width: 150px;">Tanggal</th>
                            <th class="py-3">Keterangan</th>
                            <th class="py-3">Status</th>
                            <th class="px-4 py-3 text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($history)): ?>
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">Belum ada riwayat pemeliharaan.</td>
                        </tr>
                        <?php else: ?>
                            <?php foreach($history as $h): ?>
                            <tr>
                                <td class="px-4">
                                    <div class="fw-bold text-primary"><?php echo tgl_indo($h['tanggal_pemeliharaan']); ?></div>
                                    <div class="small text-muted">Teknisi: <?php echo $h['teknisi']; ?></div>
                                </td>
                                <td class="small"><?php echo nl2br($h['keterangan']); ?></td>
                                <td>
                                    <?php if($h['status'] == 'proses'): ?>
                                        <span class="badge bg-warning text-dark">Dalam Perbaikan</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Selesai</span>
                                        <div class="small text-muted" style="font-size: 0.7rem;">Tgl: <?php echo tgl_indo($h['tanggal_selesai']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 text-end">
                                    <?php if($h['status'] == 'proses'): ?>
                                        <form action="servis_selesai.php" method="POST" class="d-inline" id="servisForm-<?= $h['id'] ?>">
                                            <?= csrf_input() ?>
                                            <input type="hidden" name="id" value="<?= $h['id'] ?>">
                                            <input type="hidden" name="sarana_id" value="<?= $sarana_id ?>">
                                            <button type="button" class="btn btn-sm btn-outline-success"
                                                onclick="showConfirmModal('Tandai perbaikan selesai?', function() { document.getElementById('servisForm-<?= $h['id'] ?>').submit(); })">
                                                <i class="fas fa-check me-1"></i> Selesai
                                            </button>
                                        </form>
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