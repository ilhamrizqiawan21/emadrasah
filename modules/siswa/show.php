<?php
// emadrasah/modules/siswa/show.php
$page_title = 'Detail Siswa';
include __DIR__ . '/../../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil Data Lengkap (Join 3 Tabel Utama)
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
    header('Location: index.php');
    exit;
}
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title">Detail Peserta Didik</h2>
        <p class="page-subtitle">Informasi lengkap Buku Induk: <strong><?php echo $s['nama_lengkap']; ?></strong></p>
    </div>
    <div class="d-flex gap-2">
        <a href="index.php" class="btn btn-light border">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
        <a href="edit.php?id=<?php echo $s['id']; ?>" class="btn btn-warning text-white">
            <i class="fas fa-edit me-2"></i>Edit Data
        </a>
        <a href="export_buku_induk.php?id=<?php echo $s['id']; ?>" class="btn btn-info text-white" target="_blank">
            <i class="fas fa-file-pdf me-2"></i>Cetak Buku Induk
        </a>
        <a href="../raport/export-pdf.php?id=<?php echo $s['id']; ?>&tahun_pelajaran_id=<?php echo $s['tahun_pelajaran_id']; ?>&semester=1" class="btn btn-success" target="_blank">
            <i class="fas fa-print me-2"></i>Cetak Raport
        </a>
    </div>
</div>

<div class="row g-4 fade-in">
    {{-- Sidebar Profile --}}
    <div class="col-xl-3">
        <div class="card border-0 shadow-sm text-center p-4">
            <div class="mb-3">
                <?php if ($s['foto']): ?>
                    <img src="<?php echo base_url('uploads/foto_siswa/' . $s['foto']); ?>" alt="Foto" class="rounded-3 shadow-sm" style="width: 150px; height: 180px; object-fit: cover;">
                <?php else: ?>
                    <div class="bg-light rounded-3 d-flex align-items-center justify-content-center mx-auto" style="width: 150px; height: 180px;">
                        <i class="fas fa-user fa-4x text-muted opacity-25"></i>
                    </div>
                <?php endif; ?>
            </div>
            <h5 class="fw-bold mb-1"><?php echo $s['nama_lengkap']; ?></h5>
            <p class="text-muted small mb-3">NIS: <?php echo $s['nis']; ?></p>
            <div class="badge bg-success-subtle text-success rounded-pill px-3 py-2 mb-2"><?php echo $s['status']; ?></div>
            <hr>
            <div class="text-start">
                <div class="small text-muted mb-1">Kelas</div>
                <div class="fw-bold mb-3"><?php echo $s['nama_kelas'] ?: '-'; ?></div>
                <div class="small text-muted mb-1">NISN</div>
                <div class="fw-bold mb-3"><?php echo $s['nisn'] ?: '-'; ?></div>
                <div class="small text-muted mb-1">Jenis Kelamin</div>
                <div class="fw-bold"><?php echo $s['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></div>
            </div>
        </div>
    </div>

    {{-- Data Tabs --}}
    <div class="col-xl-9">
        <ul class="nav nav-pills mb-3 em-tabs" id="detailTab" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-diri">A. Identitas</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-alamat">B. Alamat</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-ortu">C. Ortu/Wali</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-pendidikan">D. Pendidikan</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-dokumen">E. Brankas Digital</button>
            </li>
        </ul>

        <div class="tab-content card border-0 shadow-sm p-4">
            {{-- TAB A --}}
            <div class="tab-pane fade show active" id="tab-diri">
                <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Keterangan Diri</h6>
                <table class="table table-sm table-borderless">
                    <tr><td width="200">Nama Panggilan</td><td>: <?php echo $s['nama_panggilan'] ?: '-'; ?></td></tr>
                    <tr><td>Tempat, Tgl Lahir</td><td>: <?php echo $s['tempat_lahir'] . ', ' . tgl_indo($s['tanggal_lahir']); ?></td></tr>
                    <tr><td>NIK</td><td>: <?php echo $s['nik'] ?: '-'; ?></td></tr>
                    <tr><td>Agama</td><td>: <?php echo $s['agama'] ?: '-'; ?></td></tr>
                    <tr><td>Golongan Darah</td><td>: <?php echo $s['golongan_darah']; ?></td></tr>
                </table>
            </div>

            {{-- TAB B --}}
            <div class="tab-pane fade" id="tab-alamat">
                <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Alamat & Kontak</h6>
                <table class="table table-sm table-borderless">
                    <tr><td width="200">Alamat Jalan</td><td>: <?php echo $s['alamat'] ?: '-'; ?></td></tr>
                    <tr><td>RT / RW</td><td>: <?php echo ($s['rt'] ?: '-') . ' / ' . ($s['rw'] ?: '-'); ?></td></tr>
                    <tr><td>Desa / Kelurahan</td><td>: <?php echo $s['desa_kelurahan'] ?: '-'; ?></td></tr>
                    <tr><td>Kecamatan</td><td>: <?php echo $s['kecamatan'] ?: '-'; ?></td></tr>
                    <tr><td>Kabupaten / Kota</td><td>: <?php echo $s['kabupaten_kota'] ?: '-'; ?></td></tr>
                    <tr><td>Provinsi</td><td>: <?php echo $s['provinsi'] ?: '-'; ?></td></tr>
                    <tr><td>No. HP</td><td>: <?php echo $s['hp'] ?: '-'; ?></td></tr>
                </table>
            </div>

            {{-- TAB C --}}
            <div class="tab-pane fade" id="tab-ortu">
                <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Data Orang Tua</h6>
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-1 text-muted small">Nama Ayah</p>
                        <p class="fw-bold mb-3"><?php echo $s['nama_ayah'] ?: '-'; ?></p>
                        <p class="mb-1 text-muted small">Pekerjaan Ayah</p>
                        <p class="fw-bold"><?php echo $s['pekerjaan_ayah'] ?: '-'; ?></p>
                    </div>
                    <div class="col-md-6 border-start">
                        <p class="mb-1 text-muted small">Nama Ibu</p>
                        <p class="fw-bold mb-3"><?php echo $s['nama_ibu'] ?: '-'; ?></p>
                        <p class="mb-1 text-muted small">Pekerjaan Ibu</p>
                        <p class="fw-bold"><?php echo $s['pekerjaan_ibu'] ?: '-'; ?></p>
                    </div>
                </div>
            </div>

            {{-- TAB D --}}
            <div class="tab-pane fade" id="tab-pendidikan">
                <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">Riwayat Pendidikan</h6>
                <table class="table table-sm table-borderless">
                    <tr><td width="200">Asal Sekolah (SD/MI)</td><td>: <?php echo $s['nama_madrasah_asal'] ?: '-'; ?></td></tr>
                    <tr><td>No. Ijazah Asal</td><td>: <?php echo $s['no_ijazah_asal'] ?: '-'; ?></td></tr>
                    <tr><td>Tanggal Diterima</td><td>: <?php echo tgl_indo($s['tgl_diterima']); ?></td></tr>
                    <tr><td>Tahun Pelajaran Masuk</td><td>: <?php echo $s['tp_kode'] ?: '-'; ?></td></tr>
                </table>
            </div>

            {{-- TAB E --}}
            <div class="tab-pane fade" id="tab-dokumen">
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
                                    <div class="fw-bold small"><?php echo $doc['jenis_dokumen']; ?></div>
                                    <div class="text-muted small" style="font-size: 0.75rem;"><?php echo $doc['nama_file']; ?></div>
                                </div>
                                <a href="<?php echo base_url('uploads/dokumen_siswa/' . $doc['file_path']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
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
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
