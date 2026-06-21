<?php
// emadrasah/modules/jam_pelajaran/index.php
$page_title = 'Data Jam Pelajaran';
include __DIR__ . '/../../includes/header.php';

// Ambil semua data jam pelajaran
$stmt = $pdo->query("SELECT * FROM jam_pelajaran ORDER BY FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'), sesi_ke ASC");
$jam_list = $stmt->fetchAll();
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title"><i class="fas fa-clock me-2 text-primary"></i>Jam Pelajaran</h2>
        <p class="page-subtitle">Manajemen sesi waktu belajar harian untuk pembuatan jadwal.</p>
    </div>
    <div>
        <a href="<?php echo base_url('modules/jam_pelajaran/create.php'); ?>" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Tambah Sesi Baru
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3">Hari</th>
                    <th class="py-3 text-center">Sesi Ke-</th>
                    <th class="py-3 text-center">Waktu Mulai</th>
                    <th class="py-3 text-center">Waktu Selesai</th>
                    <th class="px-4 py-3 text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($jam_list)): ?>
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted">
                        <i class="fas fa-history fa-3x mb-3 d-block opacity-25"></i>
                        Belum ada sesi jam pelajaran yang dikonfigurasi.
                    </td>
                </tr>
                <?php else: ?>
                    <?php 
                    $currentHari = '';
                    foreach ($jam_list as $j): 
                    ?>
                    <tr>
                        <td class="px-4">
                            <?php if ($j['hari'] !== $currentHari): ?>
                                <span class="fw-bold text-dark"><?php echo $j['hari']; ?></span>
                                <?php $currentHari = $j['hari']; ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-primary border px-3">Sesi <?php echo $j['sesi_ke']; ?></span>
                        </td>
                        <td class="text-center fw-bold"><?php echo substr($j['jam_mulai'], 0, 5); ?></td>
                        <td class="text-center fw-bold"><?php echo substr($j['jam_selesai'], 0, 5); ?></td>
                        <td class="px-4 text-end">
                            <div class="btn-group">
                                <a href="<?php echo base_url('modules/jam_pelajaran/edit.php?id=' . $j['id']); ?>" class="btn btn-sm btn-light border" title="Edit">
                                    <i class="fas fa-edit text-warning"></i>
                                </a>
                                <form action="<?php echo base_url('modules/jam_pelajaran/delete.php'); ?>" method="POST" class="d-inline" id="deleteForm-<?= $j['id'] ?>">
                                    <?= csrf_input() ?>
                                    <input type="hidden" name="id" value="<?= $j['id'] ?>">
                                    <button type="button" class="btn btn-sm btn-light border" 
                                        onclick="showConfirmModal('Hapus sesi ini? Ini mungkin berdampak pada jadwal yang sudah ada.', function() { document.getElementById('deleteForm-<?= $j['id'] ?>').submit(); })" 
                                        title="Hapus">
                                        <i class="fas fa-trash text-danger"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
