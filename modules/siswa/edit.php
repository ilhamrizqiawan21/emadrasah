<?php
// emadrasah/modules/siswa/edit.php
$page_title = 'Edit Data Siswa';
include __DIR__ . '/../../includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil Data Lengkap
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
    header('Location: index.php');
    exit;
}

$kelas_list = $pdo->query("SELECT * FROM kelas ORDER BY nama_kelas ASC")->fetchAll();
$tp_list = $pdo->query("SELECT * FROM tahun_pelajaran ORDER BY kode DESC")->fetchAll();
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title">Edit Data Siswa</h2>
        <p class="page-subtitle">Memperbarui informasi Buku Induk: <strong><?php echo $s['nama_lengkap']; ?></strong></p>
    </div>
    <div>
        <a href="show.php?id=<?php echo $id; ?>" class="btn btn-light border">
            <i class="fas fa-arrow-left me-2"></i>Batal
        </a>
    </div>
</div>

<form action="update.php" method="POST" enctype="multipart/form-data">
    <?php echo csrf_input(); ?>
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    
    <ul class="nav nav-pills mb-4 em-tabs" id="editTab" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-diri" type="button">A. Keterangan Diri</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-tinggal" type="button">B. Tempat Tinggal</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-ortu" type="button">C. Orang Tua/Wali</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-pendidikan" type="button">D. Pendidikan</button></li>
    </ul>

    <div class="tab-content card border-0 shadow-sm p-4 mb-4">
        {{-- TAB A: KETERANGAN DIRI --}}
        <div class="tab-pane fade show active" id="tab-diri">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">No. Urut Induk</label>
                    <input type="number" name="no_urut" class="form-control" value="<?php echo $s['no_urut']; ?>">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="nama_lengkap" class="form-control" required value="<?php echo $s['nama_lengkap']; ?>">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Nama Panggilan</label>
                    <input type="text" name="nama_panggilan" class="form-control" value="<?php echo $s['nama_panggilan']; ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">NIS</label>
                    <input type="text" name="nis" class="form-control" required value="<?php echo $s['nis']; ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">NISN</label>
                    <input type="text" name="nisn" class="form-control" value="<?php echo $s['nisn']; ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">NIK</label>
                    <input type="text" name="nik" class="form-control" value="<?php echo $s['nik']; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-select">
                        <option value="L" <?php echo $s['jenis_kelamin'] == 'L' ? 'selected' : ''; ?>>Laki-laki</option>
                        <option value="P" <?php echo $s['jenis_kelamin'] == 'P' ? 'selected' : ''; ?>>Perempuan</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="Aktif" <?php echo $s['status'] == 'Aktif' ? 'selected' : ''; ?>>Aktif</option>
                        <option value="Lulus" <?php echo $s['status'] == 'Lulus' ? 'selected' : ''; ?>>Lulus</option>
                        <option value="Pindah" <?php echo $s['status'] == 'Pindah' ? 'selected' : ''; ?>>Pindah</option>
                        <option value="Keluar" <?php echo $s['status'] == 'Keluar' ? 'selected' : ''; ?>>Keluar</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tempat Lahir</label>
                    <input type="text" name="tempat_lahir" class="form-control" value="<?php echo $s['tempat_lahir']; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" class="form-control" value="<?php echo $s['tanggal_lahir']; ?>">
                </div>
                <div class="col-md-12">
                    <label class="form-label">Ganti Foto (Kosongkan jika tidak ingin ganti)</label>
                    <input type="file" name="foto" class="form-control">
                </div>
            </div>
        </div>

        {{-- TAB B: ALAMAT --}}
        <div class="tab-pane fade" id="tab-tinggal">
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label">Alamat Jalan</label>
                    <input type="text" name="alamat" class="form-control" value="<?php echo $s['alamat']; ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Kecamatan</label>
                    <input type="text" name="kecamatan" class="form-control" value="<?php echo $s['kecamatan']; ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Kabupaten/Kota</label>
                    <input type="text" name="kabupaten_kota" class="form-control" value="<?php echo $s['kabupaten_kota']; ?>">
                </div>
                <div class="col-md-4">
                    <label class="form-label">No. HP</label>
                    <input type="text" name="hp" class="form-control" value="<?php echo $s['hp']; ?>">
                </div>
            </div>
        </div>

        {{-- TAB C: ORTU --}}
        <div class="tab-pane fade" id="tab-ortu">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nama Ayah</label>
                    <input type="text" name="nama_ayah" class="form-control" value="<?php echo $s['nama_ayah']; ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nama Ibu</label>
                    <input type="text" name="nama_ibu" class="form-control" value="<?php echo $s['nama_ibu']; ?>">
                </div>
            </div>
        </div>

        {{-- TAB D: PENDIDIKAN --}}
        <div class="tab-pane fade" id="tab-pendidikan">
            <div class="row g-3">
                <div class="col-md-12">
                    <label class="form-label">Asal Sekolah (SD/MI)</label>
                    <input type="text" name="nama_sekolah_asal" class="form-control" value="<?php echo $s['nama_madrasah_asal']; ?>">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Diterima di Kelas</label>
                    <select name="kelas_id" class="form-select">
                        <?php foreach($kelas_list as $k): ?>
                        <option value="<?php echo $k['id']; ?>" <?php echo $s['kelas_id'] == $k['id'] ? 'selected' : ''; ?>><?php echo $k['nama_kelas']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Tahun Pelajaran</label>
                    <select name="tahun_pelajaran_id" class="form-select">
                        <?php foreach($tp_list as $tp): ?>
                        <option value="<?php echo $tp['id']; ?>" <?php echo $s['tahun_pelajaran_id'] == $tp['id'] ? 'selected' : ''; ?>><?php echo $tp['kode']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="text-end mb-5">
        <button type="submit" class="btn btn-primary px-5 fw-bold">Perbarui Data Siswa</button>
    </div>
</form>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
