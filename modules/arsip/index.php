<?php
// emadrasah/modules/arsip/index.php
$page_title = 'Arsip Akademik';
include __DIR__ . '/../../includes/header.php';

// 1. Inisialisasi Parameter Filter & Pagination
$tp_filter = isset($_GET['tahun_pelajaran_id']) ? $_GET['tahun_pelajaran_id'] : '';
$kelas_filter = isset($_GET['kelas_id']) ? $_GET['kelas_id'] : '';
$semester_filter = isset($_GET['semester']) ? $_GET['semester'] : '';
$tipe_filter = isset($_GET['tipe']) ? $_GET['tipe'] : '';

$limit = 9; // Jumlah item per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

// 2. Konfigurasi Caching Sederhana (File-based)
$cache_dir = __DIR__ . '/../../uploads/cache';
if (!is_dir($cache_dir)) mkdir($cache_dir, 0777, true);
$cache_key = md5(serialize($_GET)); // Cache unik berdasarkan filter & halaman
$cache_file = $cache_dir . "/arsip_list_$cache_key.json";
$cache_time = 60; // Cache berlaku selama 60 detik

// 3. Logika Pengambilan Data (Cache vs Database)
$params = [];
$where_clause = " WHERE 1=1";

if ($tp_filter) {
    $where_clause .= " AND a.tahun_pelajaran_id = :tp";
    $params['tp'] = $tp_filter;
}
if ($kelas_filter) {
    $where_clause .= " AND a.kelas_id = :kls";
    $params['kls'] = $kelas_filter;
}
if ($semester_filter) {
    $where_clause .= " AND a.semester = :semester";
    $params['semester'] = $semester_filter;
}
if ($tipe_filter) {
    $where_clause .= " AND a.tipe = :tipe";
    $params['tipe'] = $tipe_filter;
}

// Cek apakah cache masih valid
if (file_exists($cache_file) && (time() - filemtime($cache_file) < $cache_time)) {
    $cached_data = json_decode(file_get_contents($cache_file), true);
    $arsip_list = $cached_data['list'];
    $total_records = $cached_data['total'];
} else {
    // Hitung total records untuk pagination
    $count_sql = "SELECT COUNT(*) FROM arsip_akademik a $where_clause";
    $stmt_count = $pdo->prepare($count_sql);
    $stmt_count->execute($params);
    $total_records = $stmt_count->fetchColumn();

    // Ambil data dengan LIMIT & OFFSET
    $query_str = "SELECT a.*, tp.kode as tp_kode, k.nama_kelas 
                  FROM arsip_akademik a 
                  JOIN tahun_pelajaran tp ON a.tahun_pelajaran_id = tp.id 
                  JOIN kelas k ON a.kelas_id = k.id 
                  $where_clause 
                  ORDER BY a.created_at DESC 
                  LIMIT $limit OFFSET $offset";

    $stmt = $pdo->prepare($query_str);
    $stmt->execute($params);
    $arsip_list = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Simpan ke cache
    file_put_contents($cache_file, json_encode([
        'list' => $arsip_list,
        'total' => $total_records
    ]));
}

$total_pages = ceil($total_records / $limit);

// Build query string untuk link pagination (agar filter tidak hilang)
$query_params = $_GET;
unset($query_params['page']);
$url_query = http_build_query($query_params);

$tp_list = $pdo->query("SELECT * FROM tahun_pelajaran ORDER BY kode DESC")->fetchAll();
$kelas_list = $pdo->query("SELECT * FROM kelas ORDER BY nama_kelas ASC")->fetchAll();
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title"><i class="fas fa-archive me-2 text-primary"></i>Arsip Akademik</h2>
        <p class="page-subtitle">Penyimpanan Leger, RDM, dan dokumen administratif lainnya.</p>
    </div>
    <div>
        <a href="<?php echo base_url('modules/arsip/create.php'); ?>" class="btn btn-primary">
            <i class="fas fa-upload me-2"></i>Upload Arsip Baru
        </a>
    </div>
</div>

<!-- Filter -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold">Tahun Pelajaran</label>
                <select name="tahun_pelajaran_id" class="form-select">
                    <option value="">Semua Tahun</option>
                    <?php foreach($tp_list as $tp): ?>
                    <option value="<?php echo $tp['id']; ?>" <?php echo $tp_filter == $tp['id'] ? 'selected' : ''; ?>><?php echo $tp['kode']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold">Kelas</label>
                <select name="kelas_id" class="form-select">
                    <option value="">Semua Kelas</option>
                    <?php foreach($kelas_list as $kls): ?>
                    <option value="<?php echo $kls['id']; ?>" <?php echo $kelas_filter == $kls['id'] ? 'selected' : ''; ?>><?php echo $kls['nama_kelas']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Semester</label>
                <select name="semester" class="form-select">
                    <option value="">Semua Semester</option>
                    <option value="1" <?php echo $semester_filter == '1' ? 'selected' : ''; ?>>1 (Ganjil)</option>
                    <option value="2" <?php echo $semester_filter == '2' ? 'selected' : ''; ?>>2 (Genap)</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold">Tipe Arsip</label>
                <select name="tipe" class="form-select">
                    <option value="">Semua Tipe</option>
                    <option value="Leger" <?php echo $tipe_filter == 'Leger' ? 'selected' : ''; ?>>Leger</option>
                    <option value="RDM" <?php echo $tipe_filter == 'RDM' ? 'selected' : ''; ?>>RDM</option>
                    <option value="Lainnya" <?php echo $tipe_filter == 'Lainnya' ? 'selected' : ''; ?>>Lainnya</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="row g-4 fade-in">
    <?php if (empty($arsip_list)): ?>
    <div class="col-12 text-center py-5 text-muted bg-white rounded-4 border">
        <i class="fas fa-folder-open fa-3x mb-3 opacity-25"></i>
        <p>Arsip belum tersedia untuk kriteria ini.</p>
    </div>
    <?php else: ?>
        <?php foreach ($arsip_list as $a): ?>
        <div class="col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <?php 
                            $tipe_icon = ['Leger' => 'fa-file-invoice', 'RDM' => 'fa-file-pdf', 'Lainnya' => 'fa-file-alt'];
                            $tipe_cls = ['Leger' => 'bg-primary', 'RDM' => 'bg-success', 'Lainnya' => 'bg-secondary'];
                        ?>
                        <div class="em-user-avatar <?php echo $tipe_cls[$a['tipe']]; ?> text-white" style="width: 40px; height: 40px;">
                            <i class="fas <?php echo $tipe_icon[$a['tipe']]; ?>"></i>
                        </div>
                        <span class="badge bg-light text-dark border"><?php echo $a['tipe']; ?></span>
                    </div>
                    
                    <h6 class="fw-bold text-dark mb-1"><?php echo $a['nama_arsip']; ?></h6>
                    <p class="small text-muted mb-3">TP: <?php echo $a['tp_kode']; ?> | Kelas: <?php echo $a['nama_kelas']; ?> | Sem: <?php echo $a['semester']; ?></p>

                    <div class="d-flex gap-2">
                        <a href="<?php echo base_url('modules/arsip/download.php?id=' . $a['id']); ?>" class="btn btn-sm btn-light border w-100 fw-bold" target="_blank">
                            <i class="fas fa-download me-1 text-primary"></i> Download
                        </a>
                        <form action="<?= base_url('modules/arsip/delete.php') ?>" method="POST" class="d-inline" id="deleteForm-<?= $a['id'] ?>">
                            <?= csrf_input() ?>
                            <input type="hidden" name="id" value="<?= $a['id'] ?>">
                            <button type="button" class="btn btn-sm btn-light border" 
                                onclick="showConfirmModal('Hapus arsip ini?', function() { document.getElementById('deleteForm-<?= $a['id'] ?>').submit(); })" 
                                title="Hapus">
                                <i class="fas fa-trash text-danger"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- 4. Navigasi Pagination -->
<?php if ($total_pages > 1): ?>
<div class="mt-5 d-flex justify-content-center fade-in">
    <nav aria-label="Page navigation">
        <ul class="pagination shadow-sm">
            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $url_query ? '&'.$url_query : ''; ?>">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
            
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo $url_query ? '&'.$url_query : ''; ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>

            <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $url_query ? '&'.$url_query : ''; ?>">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        </ul>
    </nav>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
