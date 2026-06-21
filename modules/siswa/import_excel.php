<?php
// emadrasah/modules/siswa/import_excel.php
$page_title = 'Import Bulk Data Siswa';
include __DIR__ . '/../../includes/header.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_POST['import'])) {
    $file = $_FILES['file_excel']['tmp_name'];
    
    try {
        $spreadsheet = IOFactory::load($file);
        $data = $spreadsheet->getActiveSheet()->toArray();
        
        $count = 0;
        foreach ($data as $row => $col) {
            if ($row == 0) continue; // Skip header
            
            if (empty($col[3])) continue; // Skip jika nama kosong
            
            $stmt = $pdo->prepare("INSERT INTO siswa (no_urut, nis, nisn, nama_lengkap, jenis_kelamin, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $col[0], // no_urut
                $col[1], // nis
                $col[2], // nisn
                $col[3], // nama_lengkap
                $col[4], // jenis_kelamin
                $col[6]  // status
            ]);
            $count++;
        }
        
        set_flash('success', "$count data siswa berhasil diimport.");
        header("Location: index.php");
        exit;
        
    } catch (Exception $e) {
        set_flash('danger', "Terjadi kesalahan saat import: " . $e->getMessage());
    }
}
?>

<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title">Import Bulk Siswa</h2>
        <p class="page-subtitle">Unggah file Excel untuk menambah data siswa secara massal.</p>
    </div>
</div>

<div class="card border-0 shadow-sm p-4 fade-in" style="max-width: 600px;">
    <form action="" method="POST" enctype="multipart/form-data">
        <div class="mb-4">
            <label class="form-label fw-bold">Pilih File Excel (.xlsx / .xls)</label>
            <input type="file" name="file_excel" class="form-control" accept=".xlsx, .xls" required>
            <div class="form-text mt-2">
                Format file harus sesuai dengan <a href="export_excel.php" class="text-primary fw-bold">Template Excel</a>.
            </div>
        </div>
        
        <div class="alert alert-info border-0 shadow-sm small">
            <i class="fas fa-info-circle me-2"></i> 
            Pastikan kolom NIS dan NISN unik. Sistem akan melewati baris dengan nama kosong.
        </div>
        
        <div class="d-flex gap-2">
            <button type="submit" name="import" class="btn btn-primary px-4">
                <i class="fas fa-upload me-2"></i>Mulai Import
            </button>
            <a href="index.php" class="btn btn-light px-4">Batal</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
