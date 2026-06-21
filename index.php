<?php
// emadrasah/index.php
$page_title = 'Dashboard';
include __DIR__ . '/includes/header.php';

// Menghitung Statistik
$totalSiswa = $pdo->query("SELECT COUNT(*) FROM siswa")->fetchColumn();
$totalGuru = $pdo->query("SELECT COUNT(*) FROM gurus")->fetchColumn();
$totalKelas = $pdo->query("SELECT COUNT(*) FROM kelas")->fetchColumn();
$suratMasukBulanIni = $pdo->query("SELECT COUNT(*) FROM surat_masuk WHERE MONTH(tanggal_terima) = MONTH(CURRENT_DATE())")->fetchColumn();

// Data untuk Chart: Siswa per Kelas
$querySiswaKelas = $pdo->query("
    SELECT k.nama_kelas, COUNT(s.id) as total 
    FROM kelas k 
    LEFT JOIN siswa s ON k.id = s.kelas_id 
    GROUP BY k.id
");
$chartDataKelas = $querySiswaKelas->fetchAll();

// Data untuk Chart: Jenis Kelamin
$queryJK = $pdo->query("SELECT jenis_kelamin, COUNT(*) as total FROM siswa GROUP BY jenis_kelamin");
$chartDataJK = $queryJK->fetchAll();
?>

<?php display_flash(); ?>
<div class="page-header mb-4 fade-in">
    <div>
        <h2 class="page-title">Dashboard</h2>
        <p class="page-subtitle">Selamat Datang, <strong><?php echo $_SESSION['user_name']; ?></strong>. Berikut adalah ringkasan data Madrasah hari ini.</p>
    </div>
</div>

<div class="row g-4 mb-4 fade-in">
    <!-- Card Statistik -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card p-3 border-0 shadow-sm h-100 position-relative overflow-hidden card-stat">
            <div class="d-flex align-items-center gap-3 position-relative" style="z-index: 2;">
                <div class="em-stat-icon bg-primary-subtle text-primary">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-0 small uppercase fw-bold">Total Siswa</h6>
                    <h3 class="fw-bold mb-0"><?php echo number_format($totalSiswa); ?></h3>
                </div>
            </div>
            <i class="fas fa-user-graduate stat-bg-icon"></i>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card p-3 border-0 shadow-sm h-100 position-relative overflow-hidden card-stat">
            <div class="d-flex align-items-center gap-3 position-relative" style="z-index: 2;">
                <div class="em-stat-icon bg-success-subtle text-success">
                    <i class="fas fa-chalkboard-user"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-0 small uppercase fw-bold">Total Guru</h6>
                    <h3 class="fw-bold mb-0"><?php echo number_format($totalGuru); ?></h3>
                </div>
            </div>
            <i class="fas fa-chalkboard-user stat-bg-icon"></i>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card p-3 border-0 shadow-sm h-100 position-relative overflow-hidden card-stat">
            <div class="d-flex align-items-center gap-3 position-relative" style="z-index: 2;">
                <div class="em-stat-icon bg-info-subtle text-info">
                    <i class="fas fa-door-open"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-0 small uppercase fw-bold">Total Kelas</h6>
                    <h3 class="fw-bold mb-0"><?php echo number_format($totalKelas); ?></h3>
                </div>
            </div>
            <i class="fas fa-door-open stat-bg-icon"></i>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card p-3 border-0 shadow-sm h-100 position-relative overflow-hidden card-stat">
            <div class="d-flex align-items-center gap-3 position-relative" style="z-index: 2;">
                <div class="em-stat-icon bg-warning-subtle text-warning">
                    <i class="fas fa-envelope"></i>
                </div>
                <div>
                    <h6 class="text-muted mb-0 small uppercase fw-bold">Surat Masuk</h6>
                    <h3 class="fw-bold mb-0"><?php echo number_format($suratMasukBulanIni); ?></h3>
                </div>
            </div>
            <i class="fas fa-envelope stat-bg-icon"></i>
        </div>
    </div>
</div>

<div class="row g-4 mb-4 fade-in">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0">Statistik Siswa per Kelas</h5>
                <i class="fas fa-chart-bar text-muted"></i>
            </div>
            <div style="height: 300px;">
                <canvas id="chartSiswaKelas"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0">Jenis Kelamin</h5>
                <i class="fas fa-venus-mars text-muted"></i>
            </div>
            <div style="height: 300px;">
                <canvas id="chartJK"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm p-4 fade-in">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h5 class="fw-bold mb-2">Pintasan Cepat</h5>
            <p class="text-muted small mb-0">Akses fitur yang paling sering digunakan untuk mempercepat pekerjaan Anda.</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <div class="d-flex gap-2 justify-content-md-end">
                <a href="modules/siswa/create.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> Siswa Baru
                </a>
                <a href="modules/surat_masuk/create.php" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-envelope-open me-1"></i> Surat Masuk
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    const textColor = isDark ? '#94a3b8' : '#64748b';
    const borderColor = isDark ? '#334155' : '#e2e8f0';

    // Chart Siswa per Kelas
    new Chart(document.getElementById('chartSiswaKelas'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($chartDataKelas, 'nama_kelas')); ?>,
            datasets: [{
                label: 'Jumlah Siswa',
                data: <?php echo json_encode(array_column($chartDataKelas, 'total')); ?>,
                backgroundColor: '#10b981',
                borderRadius: 6,
                maxBarThickness: 40
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: borderColor },
                    ticks: { color: textColor }
                },
                x: {
                    grid: { display: false },
                    ticks: { color: textColor }
                }
            }
        }
    });

    // Chart Jenis Kelamin
    new Chart(document.getElementById('chartJK'), {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode(array_column($chartDataJK, 'jenis_kelamin')); ?>,
            datasets: [{
                data: <?php echo json_encode(array_column($chartDataJK, 'total')); ?>,
                backgroundColor: ['#0ea5e9', '#ec4899'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: textColor,
                        padding: 20,
                        usePointStyle: true
                    }
                }
            }
        }
    });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>