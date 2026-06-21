<?php
// emadrasah/modules/buku_induk/edit.php
$page_title = 'Edit Data Siswa';
include __DIR__ . '/../../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("
    SELECT s.*, o.nama_ayah, o.pekerjaan_ayah, o.nama_ibu, o.pekerjaan_ibu,
           p.nama_madrasah_asal, p.no_ijazah_asal, p.tgl_diterima
    FROM siswa s
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
function h_edit($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

$kelas_list = $pdo->query("SELECT * FROM kelas ORDER BY nama_kelas ASC")->fetchAll();
$tp_list = $pdo->query("SELECT * FROM tahun_pelajaran ORDER BY kode DESC")->fetchAll();

// Ambil dokumen yang sudah ada
$stmt_docs = $pdo->prepare("SELECT * FROM siswa_dokumen WHERE siswa_id = ?");
$stmt_docs->execute([$id]);
$existing_docs = $stmt_docs->fetchAll();
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title">Edit Data Siswa</h2>
        <p class="page-subtitle">Memperbarui informasi Buku Induk: <strong><?php echo h_edit($s['nama_lengkap']); ?></strong></p>
    </div>
    <div>
        <a href="<?php echo base_url('modules/buku_induk/show.php?id=' . $id); ?>" class="btn btn-light border">
            <i class="fas fa-arrow-left me-2"></i>Batal
        </a>
    </div>
</div>

<form action="<?php echo base_url('modules/buku_induk/update.php'); ?>" method="POST" enctype="multipart/form-data">
        <?php echo csrf_input(); ?>
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    
    <ul class="nav nav-pills mb-4 em-tabs" id="editTab" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-diri" type="button">A. Keterangan Diri</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-tinggal" type="button">B. Tempat Tinggal</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-ortu" type="button">C. Orang Tua/Wali</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-pendidikan" type="button">D. Pendidikan</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-dokumen" type="button">E. Dokumen</button></li>
    </ul>

    <div class="tab-content card border-0 shadow-sm p-4 mb-4">
        <div class="tab-pane fade show active" id="tab-diri">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">No. Urut Induk</label>
                    <input type="number" name="no_urut" class="form-control" value="<?php echo h_edit($s['no_urut']); ?>">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" class="form-control" required value="<?php echo h_edit($s['nama_lengkap']); ?>">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Nama Panggilan</label>
                    <input type="text" name="nama_panggilan" class="form-control" value="<?php echo h_edit($s['nama_panggilan']); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">NIS</label>
                    <input type="text" name="nis" class="form-control" required value="<?php echo h_edit($s['nis']); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">NISN</label>
                    <input type="text" name="nisn" class="form-control" value="<?php echo h_edit($s['nisn']); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">NIK</label>
                    <input type="text" name="nik" class="form-control" maxlength="16" value="<?php echo h_edit($s['nik']); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-select">
                        <option value="L" <?php echo $s['jenis_kelamin'] == 'L' ? 'selected' : ''; ?>>Laki-laki</option>
                        <option value="P" <?php echo $s['jenis_kelamin'] == 'P' ? 'selected' : ''; ?>>Perempuan</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" class="form-control" value="<?php echo h_edit($s['tempat_lahir']); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" class="form-control" value="<?php echo h_edit($s['tanggal_lahir']); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Agama</label>
                    <input type="text" name="agama" class="form-control" value="<?php echo h_edit($s['agama']); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Golongan Darah</label>
                    <select name="golongan_darah" class="form-select">
                        <?php $darah_opts = ['Tidak Tahu','A','B','AB','O']; foreach($darah_opts as $d): ?>
                        <option value="<?php echo $d; ?>" <?php echo $s['golongan_darah'] == $d ? 'selected' : ''; ?>><?php echo $d; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Foto Siswa</label>
                    <input type="file" name="foto" class="form-control" accept="image/*">
                    <?php if ($s['foto']): ?>
                        <small class="text-muted">Foto saat ini: <?php echo h_edit($s['foto']); ?></small>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-tinggal">
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label">Alamat</label>
                    <input type="text" name="alamat" class="form-control" value="<?php echo h_edit($s['alamat']); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">RT</label>
                    <input type="text" name="rt" class="form-control" value="<?php echo h_edit($s['rt']); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">RW</label>
                    <input type="text" name="rw" class="form-control" value="<?php echo h_edit($s['rw']); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Desa/Kelurahan</label>
                    <input type="text" name="desa_kelurahan" class="form-control" value="<?php echo h_edit($s['desa_kelurahan']); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Provinsi</label>
                    <input type="text" name="provinsi" class="form-control" value="<?php echo h_edit($s['provinsi']); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Kecamatan</label>
                    <input type="text" name="kecamatan" class="form-control" value="<?php echo h_edit($s['kecamatan']); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Kabupaten/Kota</label>
                    <input type="text" name="kabupaten_kota" class="form-control" value="<?php echo h_edit($s['kabupaten_kota']); ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">No. HP</label>
                    <input type="text" name="hp" class="form-control" value="<?php echo h_edit($s['hp']); ?>">
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-ortu">
            <h6 class="text-primary fw-bold mb-3">Data Ayah</h6>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Nama Ayah</label>
                    <input type="text" name="nama_ayah" class="form-control" value="<?php echo h_edit($s['nama_ayah']); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Pekerjaan Ayah</label>
                    <input type="text" name="pekerjaan_ayah" class="form-control" value="<?php echo h_edit($s['pekerjaan_ayah']); ?>">
                </div>
            </div>
            <h6 class="text-primary fw-bold mb-3">Data Ibu</h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nama Ibu</label>
                    <input type="text" name="nama_ibu" class="form-control" value="<?php echo h_edit($s['nama_ibu']); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Pekerjaan Ibu</label>
                    <input type="text" name="pekerjaan_ibu" class="form-control" value="<?php echo h_edit($s['pekerjaan_ibu']); ?>">
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="tab-pendidikan">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Diterima di Kelas</label>
                    <select name="kelas_id" class="form-select">
                        <?php foreach($kelas_list as $k): ?>
                        <option value="<?php echo h_edit($k['id']); ?>" <?php echo $s['kelas_id'] == $k['id'] ? 'selected' : ''; ?>><?php echo h_edit($k['nama_kelas']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tahun Pelajaran</label>
                    <select name="tahun_pelajaran_id" class="form-select">
                        <?php foreach($tp_list as $tp): ?>
                        <option value="<?php echo h_edit($tp['id']); ?>" <?php echo $s['tahun_pelajaran_id'] == $tp['id'] ? 'selected' : ''; ?>><?php echo h_edit($tp['kode']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Asal Sekolah (SD/MI)</label>
                    <input type="text" name="nama_madrasah_asal" class="form-control" value="<?php echo h_edit($s['nama_madrasah_asal']); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">No. Ijazah Asal</label>
                    <input type="text" name="no_ijazah_asal" class="form-control" value="<?php echo h_edit($s['no_ijazah_asal']); ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tanggal Diterima</label>
                    <input type="date" name="tgl_diterima" class="form-control" value="<?php echo h_edit($s['tgl_diterima']); ?>">
                </div>
            </div>
        </div>

        <!-- TAB E: DOKUMEN -->
        <div class="tab-pane fade" id="tab-dokumen">
            <!-- Dokumen yang sudah ada -->
            <?php if (!empty($existing_docs)): ?>
            <h6 class="text-primary fw-bold mb-3">Dokumen Tersimpan</h6>
            <div class="row g-3 mb-4">
                <?php foreach($existing_docs as $doc): ?>
                <div class="col-md-6">
                    <div class="d-flex align-items-center p-3 border rounded-3 bg-light">
                        <div class="flex-shrink-0">
                            <i class="fas fa-file-pdf fa-2x text-danger"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <div class="fw-bold small"><?php echo h_edit($doc['jenis_dokumen']); ?></div>
                            <div class="text-muted small" style="font-size: 0.75rem;"><?php echo h_edit($doc['nama_file']); ?></div>
                        </div>
                        <a href="<?php echo base_url('uploads/dokumen_siswa/' . rawurlencode($doc['file_path'])); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Upload dokumen baru -->
            <h6 class="text-primary fw-bold mb-3"><i class="fas fa-upload me-2"></i>Upload Dokumen Baru</h6>
            <div class="alert alert-info py-2 small">
                <i class="fas fa-info-circle me-1"></i> Format file yang didukung: PDF, JPG, PNG. Maksimal 2MB.
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Scan Akta Kelahiran</label>
                    <input type="file" name="dokumen_akta" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Scan Kartu Keluarga</label>
                    <input type="file" name="dokumen_kk" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Scan Ijazah</label>
                    <input type="file" name="dokumen_ijazah" class="form-control">
                </div>
            </div>
        </div>
    </div>

    <div class="text-end mb-5">
        <button type="submit" class="btn btn-primary px-5 fw-bold">Perbarui Data Siswa</button>
    </div>
</form>



<?php include __DIR__ . '/../../includes/footer.php'; ?>
