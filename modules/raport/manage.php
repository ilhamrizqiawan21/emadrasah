<?php
// emadrasah/modules/raport/manage.php
$page_title = 'Kelola Nilai';
include __DIR__ . '/../../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT s.*, k.nama_kelas FROM siswa s LEFT JOIN kelas k ON s.kelas_id = k.id WHERE s.id = ?");
$stmt->execute([$id]);
$siswa = $stmt->fetch();

if (!$siswa) {
    header('Location: index.php');
    exit;
}

// Data TP & Semester
$tp_list = $pdo->query("SELECT * FROM tahun_pelajaran ORDER BY kode DESC")->fetchAll();
$selectedTp = isset($_GET['tahun_pelajaran_id']) ? $_GET['tahun_pelajaran_id'] : ($pdo->query("SELECT id FROM tahun_pelajaran WHERE is_aktif = 1")->fetchColumn() ?: null);
$semester = isset($_GET['semester']) ? $_GET['semester'] : 1;

// Ambil Mata Pelajaran
$mapels = $pdo->query("SELECT * FROM mapels ORDER BY nama_mapel ASC")->fetchAll();

// Ambil Nilai yang sudah ada
$stmt_nilai = $pdo->prepare("SELECT * FROM raport_nilai WHERE siswa_id = ? AND tahun_pelajaran_id = ? AND semester = ?");
$stmt_nilai->execute([$id, $selectedTp, $semester]);
$nilai_data = $stmt_nilai->fetchAll();
$nilai = [];
foreach ($nilai_data as $n) {
    $nilai[$n['mapel_id']] = $n;
}
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title">Kelola Nilai: <?php echo $siswa['nama_lengkap']; ?></h2>
        <p class="page-subtitle">NIS: <?php echo $siswa['nis']; ?> | Kelas: <?php echo $siswa['nama_kelas'] ?: '-'; ?></p>
    </div>
    <div>
        <a href="<?php echo base_url('modules/raport/index.php'); ?>" class="btn btn-light border">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<!-- Filter TP & Semester -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="<?php echo base_url('modules/raport/manage.php'); ?>" method="GET" class="row g-3 align-items-end">
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted">Tahun Pelajaran</label>
                <select name="tahun_pelajaran_id" class="form-select">
                    <?php foreach($tp_list as $tp): ?>
                    <option value="<?php echo $tp['id']; ?>" <?php echo $tp['id'] == $selectedTp ? 'selected' : ''; ?>><?php echo $tp['kode']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted">Semester</label>
                <select name="semester" class="form-select">
                    <option value="1" <?php echo $semester == 1 ? 'selected' : ''; ?>>1 (Ganjil)</option>
                    <option value="2" <?php echo $semester == 2 ? 'selected' : ''; ?>>2 (Genap)</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Tampilkan</button>
            </div>
        </form>
    </div>
</div>

<form action="<?php echo base_url('modules/raport/store.php'); ?>" method="POST">
        <?php echo csrf_input(); ?>
    <input type="hidden" name="siswa_id" value="<?php echo $id; ?>">
    <input type="hidden" name="tahun_pelajaran_id" value="<?php echo $selectedTp; ?>">
    <input type="hidden" name="semester" value="<?php echo $semester; ?>">

    <div class="card border-0 shadow-sm overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3" style="width: 300px;">Mata Pelajaran</th>
                        <th class="py-3 text-center" style="width: 150px;">Nilai Akhir</th>
                        <th class="py-3 px-4">Capaian Kompetensi (Deskripsi)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mapels as $m): ?>
                    <?php 
                        $val_angka = $nilai[$m['id']]['nilai_akhir'] ?? '';
                        $val_capaian = $nilai[$m['id']]['capaian_kompetensi'] ?? '';
                    ?>
                    <tr>
                        <td class="px-4">
                            <div class="fw-bold"><?php echo $m['nama_mapel']; ?></div>
                            <small class="text-muted"><?php echo $m['kode_mapel']; ?></small>
                        </td>
                        <td class="text-center">
                            <input type="number" name="nilai[<?php echo $m['id']; ?>][angka]" 
                                   class="form-control text-center fw-bold mx-auto" 
                                   style="max-width: 80px;" 
                                   min="0" max="100" 
                                   value="<?php echo $val_angka; ?>">
                        </td>
                        <td class="px-4">
                            <textarea name="nilai[<?php echo $m['id']; ?>][capaian]" 
                                      class="form-control" rows="2" 
                                      placeholder="Contoh: Menunjukkan pemahaman yang sangat baik dalam..."><?php echo $val_capaian; ?></textarea>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="text-end mb-5">
        <button type="submit" class="btn btn-primary px-5 fw-bold shadow-sm">
            <i class="fas fa-save me-2"></i>Simpan Arsip Nilai
        </button>
    </div>
</form>

<?php include __DIR__ . '/../../includes/footer.php'; ?>