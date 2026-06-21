<?php
// emadrasah/modules/template_surat/preview.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM template_surat WHERE id = ?");
$stmt->execute([$id]);
$template = $stmt->fetch();

if (!$template) {
    die('Template tidak ditemukan.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Preview Template - <?php echo htmlspecialchars($template['nama_template']); ?></title>
    <style>
        body { font-family: Arial, sans-serif; padding: 2rem; }
        .card { border: 1px solid #ddd; border-radius: 0.5rem; padding: 1.5rem; background: #fff; }
        .header { margin-bottom: 1rem; }
        .header h1 { margin: 0; font-size: 1.5rem; }
        .content { white-space: pre-wrap; word-break: break-word; }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1>Preview Template</h1>
            <p><strong><?php echo htmlspecialchars($template['nama_template']); ?></strong></p>
        </div>
        <div class="content">
            <?php echo $template['konten']; ?>
        </div>
    </div>
</body>
</html>
