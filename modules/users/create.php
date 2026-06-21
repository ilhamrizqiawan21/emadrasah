<?php
// emadrasah/modules/users/create.php
$page_title = 'Tambah User';
include __DIR__ . '/../../includes/header.php';
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title">Tambah User</h2>
        <p class="page-subtitle">Buat akun baru untuk staf atau admin.</p>
    </div>
    <div>
        <a href="index.php" class="btn btn-light border">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm p-4 fade-in">
    <form action="store.php" method="POST">
        <?php echo csrf_input(); ?>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Password <span class="text-danger">*</span></label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Role</label>
                <select name="role" class="form-select">
                    <option value="operator">Operator</option>
                    <option value="admin">Admin</option>
                    <option value="guru">Guru</option>
                    <option value="wali_murid">Wali Murid</option>
                    <option value="siswa">Siswa</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Status</label>
                <select name="is_active" class="form-select">
                    <option value="1">Aktif</option>
                    <option value="0">Non-Aktif</option>
                </select>
            </div>
            <div class="col-md-12">
                <label class="form-label">Telepon</label>
                <input type="text" name="phone" class="form-control">
            </div>
            <div class="col-md-12">
                <label class="form-label">Alamat</label>
                <textarea name="alamat" class="form-control" rows="3"></textarea>
            </div>
        </div>

        <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary px-5 fw-bold">Simpan User</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>