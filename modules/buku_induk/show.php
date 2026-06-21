<?php
// emadrasah/modules/buku_induk/show.php
$page_title = 'Detail Siswa';
include __DIR__ . '/../../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("
    SELECT s.*, k.nama_kelas, tp.kode as tp_kode,
           o.nama_ayah, o.pekerjaan_ayah, o.nama_ibu, o.pekerjaan_ibu,
           p.nama_madrasah_asal, p.no_ijazah_asal, p.tgl_diterima
    FROM siswa s
    LEFT JOIN kelas k ON s.kelas_id = k.id
    LEFT JOIN tahun_pelajaran tp ON s.tahun_pelajaran_id = tp.id
    LEFT JOIN orang_tua_wali o ON o.siswa_id = s.id
    LEFT JOIN perkembangan_siswa p ON p.siswa_id = s.id
    WHERE s.id = ?
");
$stmt->execute([$id]);
$s = $stmt->fetch();

if (!$s) {
    set_flash('danger', 'Data siswa tidak ditemukan.');
    header('Location: ' . base_url('modules/buku_induk/index.php'));
    exit;
}

// Helper: escape output
function h($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

// Fetch all academic years for the dropdown
$tp_list = $pdo->query("SELECT * FROM tahun_pelajaran ORDER BY kode DESC")->fetchAll();
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title">Detail Peserta Didik</h2>
        <p class="page-subtitle">Informasi lengkap Buku Induk: <strong><?php echo h($s['nama_lengkap']); ?></strong></p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?php echo base_url('modules/buku_induk/index.php'); ?>" class="btn btn-light border">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
        <a href="<?php echo base_url('modules/buku_induk/edit.php?id=' . $s['id']); ?>" class="btn btn-warning text-white">
            <i class="fas fa-edit me-2"></i>Edit Data
        </a>
        <a href="<?php echo base_url('modules/buku_induk/export_buku_induk.php?id=' . $s['id']); ?>" class="btn btn-info text-white me-2" target="_blank">
            <i class="fas fa-file-pdf me-2"></i>Cetak Buku Induk
        </a>
        <div class="d-flex align-items-center gap-2">
            <select id="select_tahun_pelajaran" class="form-select form-select-sm" style="width: 150px;">
                <?php foreach($tp_list as $tp): ?>
                    <option value="<?php echo h($tp['id']); ?>" <?php echo ($tp['id'] == $s['tahun_pelajaran_id']) ? 'selected' : ''; ?>>
                        <?php echo h($tp['kode']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select id="select_semester" class="form-select form-select-sm" style="width: 100px;">
                <option value="1">Ganjil</option>
                <option value="2">Genap</option>
            </select>
            <a href="#" id="cetakRaportBtn" class="btn btn-success" target="_blank">
            <i class="fas fa-print me-2"></i>Cetak Raport
        </a>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const siswaId = <?php echo $s['id']; ?>;
    const cetakRaportBtn = document.getElementById('cetakRaportBtn');
    const selectTahunPelajaran = document.getElementById('select_tahun_pelajaran');
    const selectSemester = document.getElementById('select_semester');

    function updateRaportLink() {
        const tahunPelajaranId = selectTahunPelajaran.value;
        const semester = selectSemester.value;
        const baseUrl = '<?php echo base_url("modules/raport/export-pdf.php"); ?>';
        cetakRaportBtn.href = `${baseUrl}?id=${siswaId}&tahun_pelajaran_id=${tahunPelajaranId}&semester=${semester}`;
    }

    selectTahunPelajaran.addEventListener('change', updateRaportLink);
    selectSemester.addEventListener('change', updateRaportLink);
    updateRaportLink();
});
</script>

<!-- Tabs Navigation -->
<ul class="nav nav-pills mb-4 em-tabs" id="detailTab" role="tablist">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-detail-diri" type="button">A. Diri</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-detail-tinggal" type="button">B. Tempat Tinggal</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-detail-ortu" type="button">C. Orang Tua</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-detail-pendidikan" type="button">D. Pendidikan</button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-detail-dokumen" type="button">E. Dokumen</button>
    </li>
</ul>

<!-- Tab Content -->
<div class="tab-content">
    <div class="card border-0 shadow-sm p-4 mb-4">
        <div class="tab-pane fade show active" id="tab-detail-diri">
            <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Keterangan Diri</h6>
            <table class="table table-sm table-borderless">
                <tr><td width="200">Nama Lengkap</td><td>: <?php echo h($s['nama_lengkap']); ?></td></tr>
                <tr><td>NIS / NISN</td><td>: <?php echo h($s['nis']); ?> / <?php echo h($s['nisn'] ?: '-'); ?></td></tr>
                <tr><td>Tempat, Tanggal Lahir</td><td>: <?php echo h($s['tempat_lahir'] ?: '-'); ?>, <?php echo tgl_indo($s['tanggal_lahir']); ?></td></tr>
                <tr><td>Jenis Kelamin</td><td>: <?php echo $s['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></td></tr>
                <tr><td>Agama</td><td>: <?php echo h($s['agama'] ?: '-'); ?></td></tr>
            </table>
        </div>

        <div class="tab-pane fade" id="tab-detail-tinggal">
            <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Tempat Tinggal</h6>
            <table class="table table-sm table-borderless">
                <tr><td width="200">Alamat</td><td>: <?php echo h($s['alamat'] ?: '-'); ?></td></tr>
                <tr><td>RT / RW</td><td>: <?php echo h($s['rt'] ?: '-'); ?> / <?php echo h($s['rw'] ?: '-'); ?></td></tr>
                <tr><td>Desa/Kelurahan</td><td>: <?php echo h($s['desa_kelurahan'] ?: '-'); ?></td></tr>
                <tr><td>Kecamatan</td><td>: <?php echo h($s['kecamatan'] ?: '-'); ?></td></tr>
                <tr><td>Kabupaten/Kota</td><td>: <?php echo h($s['kabupaten_kota'] ?: '-'); ?></td></tr>
                <tr><td>Provinsi</td><td>: <?php echo h($s['provinsi'] ?: '-'); ?></td></tr>
            </table>
        </div>

        <div class="tab-pane fade" id="tab-detail-ortu">
            <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Orang Tua / Wali</h6>
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-1 text-muted small">Nama Ayah</p>
                    <p class="fw-bold mb-3"><?php echo h($s['nama_ayah'] ?: '-'); ?></p>
                    <p class="mb-1 text-muted small">Pekerjaan Ayah</p>
                    <p class="fw-bold"><?php echo h($s['pekerjaan_ayah'] ?: '-'); ?></p>
                </div>
                <div class="col-md-6 border-start">
                    <p class="mb-1 text-muted small">Nama Ibu</p>
                    <p class="fw-bold mb-3"><?php echo h($s['nama_ibu'] ?: '-'); ?></p>
                    <p class="mb-1 text-muted small">Pekerjaan Ibu</p>
                    <p class="fw-bold"><?php echo h($s['pekerjaan_ibu'] ?: '-'); ?></p>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-detail-pendidikan">
            <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Riwayat Pendidikan</h6>
            <table class="table table-sm table-borderless">
                <tr><td width="200">Asal Sekolah (SD/MI)</td><td>: <?php echo h($s['nama_madrasah_asal'] ?: '-'); ?></td></tr>
                <tr><td>No. Ijazah Asal</td><td>: <?php echo h($s['no_ijazah_asal'] ?: '-'); ?></td></tr>
                <tr><td>Tanggal Diterima</td><td>: <?php echo tgl_indo($s['tgl_diterima']); ?></td></tr>
                <tr><td>Tahun Pelajaran Masuk</td><td>: <?php echo h($s['tp_kode'] ?: '-'); ?></td></tr>
            </table>
        </div>

        <div class="tab-pane fade" id="tab-detail-dokumen">
            <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Dokumen Siswa (Brankas Digital)</h6>
            <?php 
                $stmt_docs = $pdo->prepare("SELECT * FROM siswa_dokumen WHERE siswa_id = ?");
                $stmt_docs->execute([$id]);
                $docs = $stmt_docs->fetchAll();
            ?>
            <div class="row g-3">
                <?php if (empty($docs)): ?>
                    <div class="col-12 text-center py-4 text-muted">Belum ada dokumen yang diunggah.</div>
                <?php else: ?>
                    <?php foreach($docs as $doc): ?>
                    <div class="col-md-6">
                        <div class="d-flex align-items-center p-3 border rounded-3 bg-light">
                            <div class="flex-shrink-0">
                                <i class="fas fa-file-pdf fa-2x text-danger"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="fw-bold small"><?php echo h($doc['jenis_dokumen']); ?></div>
                                <div class="text-muted small" style="font-size: 0.75rem;"><?php echo h($doc['nama_file']); ?></div>
                            </div>
                            <a href="<?php echo base_url('uploads/dokumen_siswa/' . rawurlencode($doc['file_path'])); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>



<?php include __DIR__ . '/../../includes/footer.php'; ?>
