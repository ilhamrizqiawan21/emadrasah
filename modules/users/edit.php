<?php
// emadrasah/modules/users/edit.php
$page_title = 'Edit User';
include __DIR__ . '/../../includes/header.php';

$user = null;
if (isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $user = $stmt->fetch();
}

if (!$user) {
    header('Location: index.php');
    exit;
}
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title">Edit User</h2>
        <p class="page-subtitle">Perbarui informasi akun user.</p>
    </div>
    <div>
        <a href="index.php" class="btn btn-light border">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm p-4 fade-in">
    <form action="update.php" method="POST">
        <?php echo csrf_input(); ?>
        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Biarkan kosong jika tidak berubah">
            </div>
            <div class="col-md-6">
                <label class="form-label">Konfirmasi Password</label>
                <input type="password" name="password_confirmation" class="form-control" placeholder="Biarkan kosong jika tidak berubah">
            </div>
            <div class="col-md-6">
                <label class="form-label">Role</label>
                <select name="role" class="form-select">
                    <?php foreach (['operator', 'admin', 'guru', 'wali_murid', 'siswa'] as $role): ?>
                        <option value="<?php echo $role; ?>" <?php echo ($user['role'] === $role) ? 'selected' : ''; ?>><?php echo ucfirst(str_replace('_', ' ', $role)); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Status</label>
                <select name="is_active" class="form-select">
                    <option value="1" <?php echo ($user['is_active'] == 1) ? 'selected' : ''; ?>>Aktif</option>
                    <option value="0" <?php echo ($user['is_active'] == 0) ? 'selected' : ''; ?>>Non-Aktif</option>
                </select>
            </div>
            <div class="col-md-12">
                <label class="form-label">Telepon</label>
                <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($user['phone']); ?>">
            </div>
            <div class="col-md-12">
                <label class="form-label">Alamat</label>
                <textarea name="alamat" class="form-control" rows="3"><?php echo htmlspecialchars($user['alamat']); ?></textarea>
            </div>
        </div>

        <div class="text-end mt-4">
            <button type="submit" class="btn btn-primary px-5 fw-bold">Perbarui User</button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>