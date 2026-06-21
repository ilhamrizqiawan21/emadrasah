<?php
// emadrasah/modules/surat_keluar/cetak.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!table_has_column($pdo, 'surat_keluar', 'template_id')) {
    die("Kolom template tidak tersedia di tabel surat keluar.");
}

$stmt = $pdo->prepare("SELECT sk.*, ts.konten as template_konten FROM surat_keluar sk LEFT JOIN template_surat ts ON sk.template_id = ts.id WHERE sk.id = ?");
$stmt->execute([$id]);
$surat = $stmt->fetch();

if (!$surat || !$surat['template_konten']) {
    die("Data surat atau template tidak ditemukan untuk dicetak.");
}

// Proses Placeholder
$konten = $surat['template_konten'];
$placeholders = [
    '{nomor_surat}' => $surat['nomor_surat'],
    '{tanggal}'      => tgl_indo($surat['tanggal_kirim']),
    '{tujuan}'       => $surat['tujuan'],
    '{perihal}'      => $surat['perihal'],
    '{lampiran}'     => $surat['lampiran'] ?: '-'
];

$konten_cetak = str_replace(array_keys($placeholders), array_values($placeholders), $konten);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak_Surat_<?php echo str_replace('/', '_', $surat['nomor_surat']); ?></title>
    <style>
        body { font-family: 'Times New Roman', serif; font-size: 12pt; line-height: 1.5; padding: 2cm; }
        .no-print { background: #f8f9fa; padding: 10px; text-align: center; border-bottom: 1px solid #ddd; margin-bottom: 20px; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button onclick="window.print()" style="padding: 10px 20px; background: #28a745; color: #fff; border: none; cursor: pointer; font-weight: bold;">
        <i class="fas fa-print"></i> CETAK SURAT SEKARANG
    </button>
</div>

<div class="isi-surat">
    <?php echo $konten_cetak; ?>
</div>

</body>
</html>
