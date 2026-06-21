<?php
// emadrasah/modules/siswa/create.php
$page_title = 'Tambah Siswa Baru';
include __DIR__ . '/../../includes/header.php';

// Ambil data pendukung
$kelas_list = $pdo->query("SELECT * FROM kelas ORDER BY nama_kelas ASC")->fetchAll();
$tp_list = $pdo->query("SELECT * FROM tahun_pelajaran ORDER BY kode DESC")->fetchAll();
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title">Tambah Siswa Baru</h2>
        <p class="page-subtitle">Silakan isi formulir Buku Induk di bawah ini dengan lengkap.</p>
    </div>
    <div>
        <a href="index.php" class="btn btn-light border">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<form action="store.php" method="POST" enctype="multipart/form-data">
        <?php echo csrf_input(); ?>
    {{-- Tabs Navigation --}}
    <ul class="nav nav-pills mb-4 em-tabs" id="studentTab" role="tablist">
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-diri" type="button">A. Keterangan Diri</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-tinggal" type="button">B. Tempat Tinggal</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-ortu" type="button">C. Orang Tua/Wali</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-pendidikan" type="button">D. Pendidikan</button>
        </li>
        <li class="nav-item">
            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-dokumen" type="button">E. Dokumen</button>
        </li>
    </ul>

    <div class="tab-content card border-0 shadow-sm p-4 mb-4">
        {{-- TAB A: KETERANGAN DIRI --}}
        <div class="tab-pane fade show active" id="tab-diri">
            <h5 class="mb-4 text-primary fw-bold"><i class="fas fa-user me-2"></i>Keterangan Tentang Diri Peserta Didik</h5>
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">No. Urut Induk</label>
                    <input type="number" name="no_urut" class="form-control">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="nama_lengkap" class="form-control" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Nama Panggilan</label>
                    <input type="text" name="nama_panggilan" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">NIS <span class="text-danger">*</span></label>
                    <input type="text" name="nis" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">NISN</label>
                    <input type="text" name="nisn" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">NIK</label>
                    <input type="text" name="nik" class="form-control" maxlength="16">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-select">
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Agama</label>
                    <input type="text" name="agama" class="form-control" value="Islam">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Golongan Darah</label>
                    <select name="golongan_darah" class="form-select">
                        <option value="Tidak Tahu">Tidak Tahu</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="AB">AB</option>
                        <option value="O">O</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Foto Siswa</label>
                    <div class="em-dropzone" id="dropzone-foto">
                        <i class="fas fa-camera"></i>
                        <p class="small mb-0">Klik atau seret foto ke sini</p>
                        <input type="file" name="foto" accept="image/*">
                        <div class="em-dropzone__preview"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TAB B: TEMPAT TINGGAL --}}
        <div class="tab-pane fade" id="tab-tinggal">
            <h5 class="mb-4 text-primary fw-bold"><i class="fas fa-map-marker-alt me-2"></i>Keterangan Tempat Tinggal</h5>
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label">Alamat Jalan</label>
                    <input type="text" name="alamat_jalan" class="form-control" placeholder="Jl. Raya No...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">RT</label>
                    <input type="text" name="rt" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">RW</label>
                    <input type="text" name="rw" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Desa / Kelurahan</label>
                    <input type="text" name="desa_kelurahan" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Kecamatan</label>
                    <input type="text" name="kecamatan" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Kabupaten / Kota</label>
                    <input type="text" name="kabupaten_kota" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Provinsi</label>
                    <input type="text" name="provinsi" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">No. Telepon / HP</label>
                    <input type="text" name="hp" class="form-control">
                </div>
            </div>
        </div>

        {{-- TAB C: ORANG TUA --}}
        <div class="tab-pane fade" id="tab-ortu">
            <h5 class="mb-3 text-primary fw-bold">Data Ayah</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Nama Ayah</label>
                    <input type="text" name="nama_ayah" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Pekerjaan Ayah</label>
                    <input type="text" name="pekerjaan_ayah" class="form-control">
                </div>
            </div>
            
            <h5 class="mb-3 text-primary fw-bold">Data Ibu</h5>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nama Ibu</label>
                    <input type="text" name="nama_ibu" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Pekerjaan Ibu</label>
                    <input type="text" name="pekerjaan_ibu" class="form-control">
                </div>
            </div>
        </div>

        {{-- TAB D: PENDIDIKAN --}}
        <div class="tab-pane fade" id="tab-pendidikan">
            <h5 class="mb-4 text-primary fw-bold">Pendidikan Sebelumnya</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Asal Sekolah (SD/MI)</label>
                    <input type="text" name="nama_sekolah_asal" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">No. Ijazah</label>
                    <input type="text" name="no_ijazah_asal" class="form-control">
                </div>
            </div>

            <h5 class="mb-4 text-primary fw-bold">Penerimaan di Madrasah Ini</h5>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Diterima di Kelas</label>
                    <select name="kelas_id" class="form-select">
                        <?php foreach($kelas_list as $k): ?>
                        <option value="<?php echo $k['id']; ?>"><?php echo $k['nama_kelas']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tahun Pelajaran</label>
                    <select name="tahun_pelajaran_id" class="form-select">
                        <?php foreach($tp_list as $tp): ?>
                        <option value="<?php echo $tp['id']; ?>" <?php echo $tp['is_aktif'] ? 'selected' : ''; ?>><?php echo $tp['kode']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tanggal Diterima</label>
                    <input type="date" name="tgl_diterima" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>
        </div>

        {{-- TAB E: DOKUMEN --}}
        <div class="tab-pane fade" id="tab-dokumen">
            <h5 class="mb-4 text-primary fw-bold"><i class="fas fa-file-pdf me-2"></i>E-Document (Scan)</h5>
            <div class="alert alert-info py-2 small">
                <i class="fas fa-info-circle me-1"></i> Format file yang didukung: PDF, JPG, PNG. Maksimal 2MB.
            </div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Scan Akta Kelahiran</label>
                    <div class="em-dropzone">
                        <i class="fas fa-file-invoice"></i>
                        <p class="small mb-0">Klik atau seret file Akta</p>
                        <input type="file" name="dokumen_akta" accept=".pdf,image/*">
                        <div class="em-dropzone__preview"></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Scan Kartu Keluarga</label>
                    <div class="em-dropzone">
                        <i class="fas fa-file-invoice"></i>
                        <p class="small mb-0">Klik atau seret file KK</p>
                        <input type="file" name="dokumen_kk" accept=".pdf,image/*">
                        <div class="em-dropzone__preview"></div>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Scan Ijazah</label>
                    <div class="em-dropzone">
                        <i class="fas fa-file-invoice"></i>
                        <p class="small mb-0">Klik atau seret file Ijazah</p>
                        <input type="file" name="dokumen_ijazah" accept=".pdf,image/*">
                        <div class="em-dropzone__preview"></div>
                    </div>
                </div>
            </div>
        </div>
        </div>

        <div class="text-end mb-5">
        <button type="reset" class="btn btn-light px-4 me-2">Reset</button>
        <button type="submit" class="btn btn-primary px-5 fw-bold">Simpan Data Siswa</button>
        </div>
        </form>

        <?php include __DIR__ . '/../../includes/footer.php'; ?>