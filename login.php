<?php
// emadrasah/login.php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Jika sudah login, lempar ke dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_request()) {
        $error = 'Token CSRF tidak valid. Silakan muat ulang halaman.';
    } else {
        $username = input_safe($_POST['username']);
        $password = $_POST['password'];

        // Query cek user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR name = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();

    // Verifikasi Password (Laravel menggunakan bcrypt, PHP Native bisa pakai password_verify)
    // Catatan: Pastikan user di DB memiliki hash password yang valid.
    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = !empty($user['role']) ? ucfirst(str_replace('_', ' ', $user['role'])) : 'Administrator';
        
        header('Location: index.php');
        exit;
    } else {
        $error = 'Username atau Password salah.';
    }
}
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — e-Madrasah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        body { background: #f1f5f9; font-family: 'Plus Jakarta Sans', sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .login-card { width: 100%; max-width: 400px; border: none; border-radius: 20px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); }
        .btn-primary { background: #047857; border: none; padding: 12px; border-radius: 10px; font-weight: 700; }
        .btn-primary:hover { background: #065f46; }
        .brand-logo { width: 64px; height: 64px; background: #047857; border-radius: 15px; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; }
    </style>
</head>
<body>

<div class="card login-card p-4">
    <div class="card-body">
        <div class="brand-logo">
            <img src="assets/images/logo.png" alt="Logo" width="32">
        </div>
        <h4 class="text-center fw-bold mb-1">Selamat Datang</h4>
        <p class="text-center text-muted small mb-4">Masuk ke Sistem e-Madrasah</p>

        <?php if($error): ?>
            <div class="alert alert-danger py-2 small text-center"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="" method="POST">
            <?php echo csrf_input(); ?>
            <div class="mb-3">
                <label class="form-label small fw-bold">Email / Username</label>
                <input type="text" name="username" class="form-control" required placeholder="Masukkan email atau nama">
            </div>
            <div class="mb-4">
                <label class="form-label small fw-bold">Password</label>
                <input type="password" name="password" class="form-control" required placeholder="••••••••">
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Masuk Sekarang</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>