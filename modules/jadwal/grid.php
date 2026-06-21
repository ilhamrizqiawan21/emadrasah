<?php
// emadrasah/modules/jadwal/grid.php
$page_title = 'Input Jadwal';
include __DIR__ . '/../../includes/header.php';

// ===== 1. Inisialisasi Parameter Filter =====
$semester = isset($_GET['semester']) ? (int)$_GET['semester'] : 1;
$tahun_manual = isset($_GET['tahun_manual']) ? trim($_GET['tahun_manual']) : '';

if (empty($tahun_manual)) {
    $tapel = $pdo->query("SELECT * FROM tahun_pelajaran WHERE is_aktif = 1 LIMIT 1")->fetch();
    $tahun_manual = $tapel ? $tapel['kode'] : '';
}

// ===== 2. Pengambilan Data Master =====
// Dapatkan daftar tahun pelajaran untuk filter dropdown
$daftar_tp = $pdo->query("SELECT * FROM tahun_pelajaran ORDER BY kode DESC")->fetchAll();

// Ambil data Kelas
$kelas = $pdo->query("SELECT * FROM kelas ORDER BY nama_kelas ASC")->fetchAll();

// Ambil data Jam Pelajaran (Urut Hari & Sesi)
$orderHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
$jamPelajaranRaw = $pdo->query("SELECT DISTINCT hari, sesi_ke, jam_mulai, jam_selesai FROM jam_pelajaran ORDER BY sesi_ke ASC")->fetchAll();

// Sortir jamPelajaran secara manual sesuai urutan hari sekolah
usort($jamPelajaranRaw, function($a, $b) use ($orderHari) {
    $posA = array_search($a['hari'], $orderHari);
    $posB = array_search($b['hari'], $orderHari);
    if ($posA === false) $posA = 99;
    if ($posB === false) $posB = 99;
    if ($posA == $posB) return $a['sesi_ke'] - $b['sesi_ke'];
    return $posA - $posB;
});

// Mapping data guru ke Array untuk pencarian cepat di JavaScript
$gurus = $pdo->query("SELECT id, kode, nama, bidang_studi FROM gurus ORDER BY kode ASC")->fetchAll();
$guruMap = [];
foreach ($gurus as $g) {
    // Normalisasi kode guru (trim dan uppercase) untuk memudahkan lookup di JavaScript
    $key = trim(strtoupper($g['kode']));
    $guruMap[$key] = $g;
}

// Ambil data Mapel
$mapels = $pdo->query("SELECT * FROM mapels ORDER BY nama_mapel ASC")->fetchAll();

// ===== 3. Konstruksi Grid Jadwal Terisi =====
$stmtJadwal = $pdo->prepare("
    SELECT j.*, g.kode as guru_kode 
    FROM jadwals j 
    LEFT JOIN gurus g ON j.guru_id = g.id
    WHERE j.semester = ? AND j.tahun_ajaran = ?
");
$stmtJadwal->execute([$semester, $tahun_manual]);
$jadwals = $stmtJadwal->fetchAll();

$jadwalGrid = [];
foreach ($jadwals as $j) {
    $key = $j['kelas_id'] . '_' . $j['hari'] . '_' . $j['jam_mulai'] . '_' . $j['jam_selesai'];
    if (!isset($jadwalGrid[$key])) {
        $jadwalGrid[$key] = ['ids' => [], 'codes' => [], 'guru_ids' => []];
    }
    $jadwalGrid[$key]['ids'][] = $j['id'];
    $jadwalGrid[$key]['codes'][] = $j['guru_kode'];
    $jadwalGrid[$key]['guru_ids'][] = $j['guru_id'];
}
?>

<style>
    /* ===== GRID REDESIGN - Lebih Profesional & Responsif ===== */
    .grid-scroll-shell {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);
        border: 1px solid #e2e8f0;
        overflow: auto;
        max-height: calc(100vh - 250px);
    }

    .grid-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 0.8rem;
        min-width: 900px;
    }

    /* Sticky Header & Column */
    .grid-table thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #1e293b;
        color: #fff;
        font-weight: 700;
        padding: 10px 8px;
        white-space: nowrap;
        text-align: center;
        border: 1px solid #334155;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .col-header-jam {
        min-width: 120px;
        left: 0;
        z-index: 20 !important;
        background: #0f172a !important;
    }

    .col-header {
        min-width: 130px;
    }

    .sesi-cell {
        position: sticky;
        left: 0;
        z-index: 5;
        background: #f1f5f9 !important;
        border-right: 2px solid #cbd5e1;
        min-width: 120px;
        padding: 8px 10px !important;
        text-align: center;
        vertical-align: middle;
    }

    /* Row Styling - Hari */
    .day-row td {
        background: linear-gradient(135deg, #f0fdf4, #dcfce7) !important;
        padding: 10px 20px;
        border-bottom: 2px solid #bbf7d0;
    }

    .day-label {
        font-weight: 800;
        color: #166534;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .day-label i {
        font-size: 1rem;
        opacity: 0.7;
    }

    /* Input Cell */
    .input-cell {
        padding: 5px !important;
        border-bottom: 1px solid #e2e8f0;
        border-right: 1px solid #e2e8f0;
        transition: background 0.2s;
        height: 96px;
        vertical-align: top;
    }

    .input-cell:hover {
        background-color: #f8fafc;
    }

    .input-wrapper {
        display: flex;
        flex-direction: column;
        gap: 4px;
        height: 100%;
    }

    /* ===== GRID INPUT - KODE GURU ===== */
    .grid-input {
        width: 100%;
        border: 2px solid #cbd5e1 !important;
        border-radius: 6px !important;
        text-align: center;
        font-weight: 700;
        color: #0f172a;
        transition: all 0.2s ease;
        background: #fff;
        font-size: 1.2rem !important;
        letter-spacing: 1px;
        padding: 6px 4px !important;
        height: 40px !important;
        min-height: 40px !important;
    }

    .grid-input::placeholder {
        color: #94a3b8;
        font-weight: 500;
        font-size: 0.7rem;
        letter-spacing: 0;
    }

    .grid-input:focus {
        outline: none !important;
        border-color: #2563eb !important;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.2) !important;
        background: #fff !important;
        z-index: 10;
        transform: scale(1.02);
    }

    /* ===== STATE COLORS - Responsif ===== */
    /* STATE: Terisi (Filled) */
    .grid-input.has-value {
        background-color: #ecfdf5;
        color: #065f46;
        border-color: #6ee7b7 !important;
        box-shadow: 0 0 0 1px #a7f3d0 inset;
    }

    /* STATE: Berubah (Changed) */
    .grid-input.changed {
        background-color: #fffbeb !important;
        color: #92400e !important;
        border-color: #fbbf24 !important;
        border-style: solid !important;
        box-shadow: 0 0 0 2px #fde68a inset;
        animation: pulseChanged 1.5s ease-in-out;
    }

    @keyframes pulseChanged {
        0% { box-shadow: 0 0 0 2px #fde68a inset; }
        50% { box-shadow: 0 0 0 4px #fbbf24 inset; }
        100% { box-shadow: 0 0 0 2px #fde68a inset; }
    }

    /* STATE: Error / Conflict */
    .grid-input.conflict {
        background-color: #fef2f2 !important;
        color: #991b1b !important;
        border-color: #f87171 !important;
        border-style: solid !important;
        box-shadow: 0 0 0 2px #fecaca inset;
        animation: shakeError 0.5s ease-in-out;
    }

    /* STATE: Kosong (Empty) */
    .grid-input:placeholder-shown {
        background-color: #fafafa;
        border-color: #e2e8f0 !important;
        opacity: 0.7;
    }

    @keyframes shakeError {
        0%, 100% { transform: translateX(0); }
        10%, 50%, 90% { transform: translateX(-3px); }
        30%, 70% { transform: translateX(3px); }
    }

    /* Badge level di dalam input */
    .slot-badge {
        font-size: 0.65rem;
        color: #64748b;
        text-align: left;
        padding: 1px 4px;
        font-weight: 600;
    }

    /* Buttons & UI Elements */
    .btn-kode {
        background: #fff;
        border: 1px solid #e2e8f0;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 600;
        color: #475569;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .btn-kode:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #1e293b;
    }

    .sesi-no {
        font-weight: 800;
        font-size: 1rem;
        color: #1e293b;
        display: block;
        line-height: 1.3;
    }
    .sesi-time {
        font-weight: 600;
        font-size: 0.75rem;
        color: #64748b;
        display: block;
        line-height: 1.2;
    }

    /* Legend Baru */
    .legend-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 16px 24px;
        margin-bottom: 16px;
        background: #fff;
        padding: 12px 20px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        font-size: 0.78rem;
    }

    .legend-item {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #475569;
        font-weight: 500;
    }

    .legend-box {
        width: 18px;
        height: 18px;
        border-radius: 4px;
        border: 2px solid;
        display: inline-block;
    }
    .legend-box.filled { background: #ecfdf5; border-color: #6ee7b7; }
    .legend-box.changed { background: #fffbeb; border-color: #fbbf24; }
    .legend-box.conflict { background: #fef2f2; border-color: #f87171; }
    .legend-box.empty { background: #fafafa; border-color: #e2e8f0; }

    /* Filter select styling */
    .filter-select-tp {
        border: 2px solid #e2e8f0 !important;
        border-radius: 8px !important;
        padding: 6px 12px !important;
        font-weight: 600;
        background: #fff;
    }
    .filter-select-tp:focus {
        border-color: #059669 !important;
        box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.15) !important;
    }
</style>

<?php display_flash(); ?>
<div class="page-header fade-in">
    <div>
        <h2 class="page-title"><i class="fas fa-table-cells me-2 text-primary"></i>Input Jadwal</h2>
        <div class="d-flex align-items-center gap-3 mt-2 flex-wrap">
            <div class="filter-item">
                <label class="small fw-bold text-muted d-block">Tahun Pelajaran</label>
                <select id="filter_tahun" class="form-select form-select-sm filter-select-tp">
                    <?php foreach ($daftar_tp as $tp): ?>
                        <option value="<?php echo htmlspecialchars($tp['kode']); ?>" 
                            <?php echo $tp['kode'] === $tahun_manual ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($tp['kode']); ?>
                            <?php echo $tp['is_aktif'] ? ' (Aktif)' : ''; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-item">
                <label class="small fw-bold text-muted d-block">Semester</label>
                <select id="filter_semester" class="form-select form-select-sm filter-select-tp">
                    <option value="1" <?php echo $semester == 1 ? 'selected' : ''; ?>>Ganjil</option>
                    <option value="2" <?php echo $semester == 2 ? 'selected' : ''; ?>>Genap</option>
                </select>
            </div>
            <div class="filter-item align-self-end">
                <button type="button" onclick="applyFilters()" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-sync-alt me-1"></i>Terapkan
                </button>
            </div>
        </div>
    </div>
    <div class="d-flex gap-2 align-items-end flex-wrap">
        <a href="<?php echo base_url('modules/jadwal/index.php?semester=' . $semester . '&tahun_manual=' . urlencode($tahun_manual)); ?>" class="btn-kode">
            <i class="fas fa-arrow-left"></i> Dashboard
        </a>
        <button type="button" class="btn-kode" data-bs-toggle="modal" data-bs-target="#guruListModal">
            <i class="fas fa-id-card-alt"></i> Kode Guru
        </button>
        <a href="<?php echo base_url('modules/jadwal/cetak_jadwal.php?semester=' . $semester . '&tahun_manual=' . urlencode($tahun_manual)); ?>" target="_blank" class="btn-kode bg-success text-white border-0">
            <i class="fas fa-print"></i> Cetak
        </a>
        <button class="btn-save" id="btnSaveAll" disabled>
            <i class="fas fa-cloud-arrow-up"></i> Simpan Semua
            <span class="changes-pill" id="changesCount" style="display:none">0</span>
        </button>
    </div>
</div>

<div class="legend-grid fade-in">
    <span class="legend-item">
        <span class="legend-box filled"></span>
        <span><strong>Terisi</strong> — Kode Guru valid</span>
    </span>
    <span class="legend-item">
        <span class="legend-box changed"></span>
        <span><strong>Berubah</strong> — Data diubah, belum disimpan</span>
    </span>
    <span class="legend-item">
        <span class="legend-box conflict"></span>
        <span><strong>Error</strong> — Kode tidak ditemukan / duplikat</span>
    </span>
    <span class="legend-item">
        <span class="legend-box empty"></span>
        <span><strong>Kosong</strong> — Belum diisi</span>
    </span>
</div>

<div class="grid-scroll-shell fade-in">
    <table class="grid-table">
        <thead>
            <tr>
                <th class="col-header-jam">Jam Pelajaran</th>
                <?php foreach($kelas as $k): ?>
                    <th class="col-header"><?php echo $k['nama_kelas']; ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php $currentHari = null; ?>
            <?php foreach($jamPelajaranRaw as $jam): ?>
                <?php if($jam['hari'] !== $currentHari): ?>
                    <?php $currentHari = $jam['hari']; ?>
                    <tr class="day-row">
                        <td colspan="<?php echo count($kelas) + 1; ?>">
                            <span class="day-label"><i class="fas fa-calendar-day"></i> <?php echo $jam['hari']; ?></span>
                         </td>
                     </tr>
                <?php endif; ?>

                <tr>
                    <td class="sesi-cell">
                        <span class="sesi-no">Sesi <?php echo $jam['sesi_ke']; ?></span>
                        <span class="sesi-time"><?php echo substr($jam['jam_mulai'],0,5); ?> – <?php echo substr($jam['jam_selesai'],0,5); ?></span>
                    </td>

                    <?php foreach($kelas as $k): ?>
                        <?php 
                            $key = $k['id'] . '_' . $jam['hari'] . '_' . $jam['jam_mulai'] . '_' . $jam['jam_selesai'];
                            $existing = $jadwalGrid[$key] ?? null;
                        ?>
                        <td class="input-cell" 
                            data-kelas-id="<?php echo $k['id']; ?>"
                            data-hari="<?php echo $jam['hari']; ?>"
                            data-jam-mulai="<?php echo $jam['jam_mulai']; ?>"
                            data-jam-selesai="<?php echo $jam['jam_selesai']; ?>">
                            <div class="input-wrapper">
                                <?php for($i=0; $i<2; $i++): 
                                    $currentKode = $existing['codes'][$i] ?? '';
                                    $currentGId = $existing['guru_ids'][$i] ?? '';
                                    // Cari mapel berdasarkan guru
                                    $currentMapel = '';
                                    if ($currentGId) {
                                        foreach ($gurus as $g) {
                                            if ($g['id'] == $currentGId) {
                                                foreach ($mapels as $m) {
                                                    if ($g['bidang_studi'] && stripos($m['nama_mapel'], $g['bidang_studi']) !== false) {
                                                        $currentMapel = $m['nama_mapel'];
                                                        break;
                                                    }
                                                }
                                                break;
                                            }
                                        }
                                    }
                                ?>
                                <div class="d-flex align-items-center gap-1">
                                    <input type="text" 
                                           id="input-<?php echo $k['id']; ?>-<?php echo $jam['sesi_ke']; ?>-<?php echo $i; ?>"
                                           name="jadwal[<?php echo $k['id']; ?>][<?php echo $jam['sesi_ke']; ?>][<?php echo $i; ?>]"
                                           class="grid-input <?php echo $currentKode ? 'has-value' : ''; ?>"
                                           data-slot-index="<?php echo $i; ?>"
                                           data-guru-id="<?php echo $currentGId; ?>"
                                           data-original="<?php echo $currentKode; ?>"
                                           value="<?php echo $currentKode; ?>"
                                           placeholder="Kode G<?php echo $i+1; ?>"
                                           autocomplete="off">
                                </div>
                                <?php endfor; ?>
                            </div>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Status Bar -->
<div class="status-bar fade-in">
    <div class="status-text">
        <span class="status-dot" id="statusDot"></span>
        <span id="statusLabel">Siap.</span>
    </div>
    <button class="btn-save" id="btnSaveAllBottom" disabled>
        <i class="fas fa-cloud-arrow-up me-2"></i><span>Simpan Semua</span>
    </button>
</div>

<!-- Modal Daftar Kode Guru -->
<div class="modal fade" id="guruListModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Daftar Kode Guru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="input-group mb-3">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="searchGuru" name="search_guru" class="form-control" placeholder="Cari kode, nama, atau bidang studi…">
                </div>
                <div class="table-responsive" style="max-height:420px; overflow:auto;">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th style="width:80px">Kode</th>
                                <th>Nama Guru</th>
                                <th>Bidang Studi</th>
                                <th style="width:60px"></th>
                            </tr>
                        </thead>
                        <tbody id="guruListBody">
                            <?php foreach($gurus as $g): ?>
                            <tr class="guru-row"
                                data-kode="<?php echo strtolower($g['kode']); ?>"
                                data-nama="<?php echo strtolower($g['nama']); ?>"
                                data-bidang="<?php echo strtolower($g['bidang_studi'] ?? ''); ?>">
                                <td><span class="badge bg-secondary"><?php echo $g['kode']; ?></span></td>
                                <td class="fw-semibold"><?php echo $g['nama']; ?></td>
                                <td class="text-muted small"><?php echo $g['bidang_studi'] ?: '-'; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-success py-0 px-2" type="button" onclick="insertKode('<?php echo $g['kode']; ?>')" title="Sisipkan kode ini">
                                        <i class="fas fa-arrow-left"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tooltip element -->
<div id="guruTooltip" class="guru-tooltip">
    <div class="tt-name" id="ttName"></div>
    <div class="tt-sub" id="ttSub"></div>
</div>

<script>
const GURU_DATA = <?php echo json_encode($guruMap); ?>;
const MAPEL_DATA = <?php echo json_encode($mapels); ?>;
const SAVE_URL = '<?php echo base_url("modules/jadwal/save_grid.php"); ?>';
const RESOLVE_URL = '<?php echo base_url("modules/jadwal/resolve_kode.php"); ?>';
const SEMESTER = <?php echo $semester; ?>;
const TAHUN_AJARAN = '<?php echo $tahun_manual; ?>';

// Mengarahkan ulang halaman saat filter diubah
function applyFilters() {
    const tahun = document.getElementById('filter_tahun').value;
    const semester = document.getElementById('filter_semester').value;
    if (!tahun) {
        alert('Tahun Pelajaran harus diisi!');
        return;
    }
    const baseUrl = '<?php echo base_url("modules/jadwal/grid.php"); ?>';
    window.location.href = `${baseUrl}?semester=${semester}&tahun_manual=${encodeURIComponent(tahun)}`;
}

// ===== CELL STATE MANAGEMENT =====
// Mengelola feedback visual (warna) pada input grid berdasarkan kondisi data
function updateCellVisualState(inputEl) {
    const val = inputEl.value.trim().toUpperCase();
    
    // Reset semua state class
    inputEl.classList.remove('has-value', 'changed', 'conflict');
    
    if (val === '') {
        // STATE: Kosong — biarkan default (border abu tipis)
        return;
    }

    if (inputEl.dataset.errorType === 'duplicate' || inputEl.dataset.errorType === 'notfound') {
        // STATE: Error — sudah ditangani oleh checkRowConflicts / resolveGuruCode
        return;
    }

    const isOriginal = val === (inputEl.dataset.original || '');
    
    if (GURU_DATA[val]) {
        // STATE: Terisi — kode guru valid
        inputEl.classList.add('has-value');
        if (!isOriginal) {
            // STATE: Terisi + Berubah — kombinasi
            inputEl.classList.add('changed');
        }
    } else if (!isOriginal) {
        // STATE: Berubah — nilai diubah tapi belum tervalidasi
        inputEl.classList.add('changed');
    }
}

let pendingChanges = new Map();
let currentInput = null;

function insertKode(kode) {
    if (currentInput) {
        let currentVal = currentInput.value.trim();
        if (currentVal && !currentVal.includes(kode)) {
            currentInput.value = currentVal + ", " + kode;
        } else {
            currentInput.value = kode;
        }
        validateInput(currentInput);
        markAsChanged(currentInput);
        updateCellVisualState(currentInput);
        currentInput.focus();
        const modalEl = document.getElementById('guruListModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
    }
}

document.querySelectorAll('.grid-input').forEach(input => {
    input.addEventListener('focus', function() {
        currentInput = this;
        this.select();
        const val = this.value.trim().toUpperCase();
        if (val && GURU_DATA[val]) showTooltip(this);
    });

    input.addEventListener('input', function() {
        validateInput(this);
        markAsChanged(this);
        updateCellVisualState(this);
    });

    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            validateInput(this);
            markAsChanged(this);
            updateCellVisualState(this);
            focusNextCell(this);
        } else if (e.key === 'Escape') {
            this.value = this.dataset.original || '';
            validateInput(this);
            markAsChanged(this);
            updateCellVisualState(this);
        } else if (e.key === 'Delete' || e.key === 'Backspace') {
            if (e.key === 'Delete') {
                this.value = '';
                validateInput(this);
                markAsChanged(this);
                updateCellVisualState(this);
            }
        } else if (e.key === 'ArrowRight' && this.selectionStart === this.value.length) {
            e.preventDefault();
            focusNextCell(this);
        } else if (e.key === 'ArrowLeft' && this.selectionStart === 0) {
            e.preventDefault();
            focusPrevCell(this);
        } else if (e.key === 'ArrowDown') {
            e.preventDefault();
            focusVertical(this, 'down');
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            focusVertical(this, 'up');
        } else if (e.key === 'Tab') {
            e.preventDefault();
            focusNextCell(this);
        }
    });

    input.addEventListener('mouseenter', function() { showTooltip(this); });
    input.addEventListener('mouseleave', hideTooltip);
});

// ===== Cek Konflik Duplikat Guru dalam Satu Baris (Sesi Sama, Kelas Berbeda) =====
function checkRowConflicts(inputEl) {
    const row = inputEl.closest('tr');
    if (!row) return;
    const inputsInRow = Array.from(row.querySelectorAll('.grid-input'));
    const values = {};

    // Reset error duplikat dulu
    inputsInRow.forEach(inp => {
        if (inp.dataset.errorType === 'duplicate') {
            inp.classList.remove('conflict');
            delete inp.dataset.errorType;
        }
    });

    // Kumpulkan nilai
    inputsInRow.forEach(inp => {
        const val = inp.value.trim().toUpperCase();
        if (val !== '') {
            if (!values[val]) values[val] = [];
            values[val].push(inp);
        }
    });

    // Tandai yang duplikat
    let hasConflict = false;
    for (const val in values) {
        if (values[val].length > 1) {
            values[val].forEach(inp => {
                inp.classList.remove('has-value');
                inp.classList.add('conflict');
                inp.dataset.errorType = 'duplicate';
            });
            hasConflict = true;
        }
    }
    return hasConflict;
}

// Jalankan pengecekan konflik saat pertama kali dimuat
setTimeout(() => {
    document.querySelectorAll('tr:not(.day-row)').forEach(row => {
        const first = row.querySelector('.grid-input');
        if (first) checkRowConflicts(first);
    });
    // Update visual state untuk semua input
    document.querySelectorAll('.grid-input').forEach(inp => updateCellVisualState(inp));
}, 500);

function validateInput(el) {
    const val = el.value.toUpperCase().trim();
    el.value = val;
    
    if (val === '') {
        el.classList.remove('conflict', 'has-value', 'changed');
        el.dataset.guruId = '';
        el.dataset.mapelId = '';
        checkRowConflicts(el);
        return;
    }

    const guru = GURU_DATA[val];
    if (guru) {
        applyGuruToCell(el, guru);
        checkRowConflicts(el);
        return;
    }

    resolveGuruCode(el, val);
}

function applyGuruToCell(el, guru) {
    el.classList.remove('conflict');
    el.classList.add('has-value');
    el.dataset.guruId = guru.id;

    let mapel = null;
    if (guru.bidang_studi) {
        mapel = MAPEL_DATA.find(m => m.nama_mapel.toLowerCase().includes(guru.bidang_studi.toLowerCase()));
    }
    if (mapel) {
        el.dataset.mapelId = mapel.id;
    } else {
        el.dataset.mapelId = el.dataset.mapelId || '';
    }
}

async function resolveGuruCode(el, kode) {
    try {
        const response = await fetch(`${RESOLVE_URL}?kode=${encodeURIComponent(kode)}`);
        if (!response.ok) {
            el.classList.add('conflict');
            el.classList.remove('has-value');
            el.dataset.guruId = '';
            el.dataset.mapelId = '';
            el.dataset.errorType = 'notfound';
            markAsChanged(el);
            checkRowConflicts(el);
            updateCellVisualState(el);
            return;
        }

        const data = await response.json();
        if (data.success && data.guru) {
            applyGuruToCell(el, data.guru);
            if (data.mapel) {
                el.dataset.mapelId = data.mapel.id;
            }
            delete el.dataset.errorType;
        } else {
            el.classList.add('conflict');
            el.classList.remove('has-value');
            el.dataset.guruId = '';
            el.dataset.mapelId = '';
            el.dataset.errorType = 'notfound';
        }
        markAsChanged(el);
        checkRowConflicts(el);
        updateCellVisualState(el);
    } catch (err) {
        el.classList.add('conflict');
        el.classList.remove('has-value');
        el.dataset.guruId = '';
        el.dataset.mapelId = '';
        el.dataset.errorType = 'notfound';
        markAsChanged(el);
        checkRowConflicts(el);
        updateCellVisualState(el);
    }
}

function markAsChanged(el) {
    const cell = el.closest('.input-cell');
    if (!cell) return;
    
    const inputs = cell.querySelectorAll('.grid-input');
    const key = `${cell.dataset.kelasId}_${cell.dataset.hari}_${cell.dataset.jamMulai}_${cell.dataset.jamSelesai}`;
    
    let isChanged = false;
    let guruIds = [];

    inputs.forEach(inp => {
        const changed = inp.value !== inp.dataset.original;
        if (changed) isChanged = true;
        if (inp.dataset.guruId) guruIds.push(inp.dataset.guruId);
    });

    if (isChanged) {
        pendingChanges.set(key, {
            kelas_id: cell.dataset.kelasId,
            guru_ids: guruIds,
            hari: cell.dataset.hari,
            jam_mulai: cell.dataset.jamMulai,
            jam_selesai: cell.dataset.jamSelesai,
            action: 'sync_slot'
        });
    } else {
        pendingChanges.delete(key);
    }

    updateSaveBtn();
}

const btnSaveTop = document.getElementById('btnSaveAll');
const btnSaveBottom = document.getElementById('btnSaveAllBottom');
const countBadge = document.getElementById('changesCount');
const statusDot = document.getElementById('statusDot');
const statusLabel = document.getElementById('statusLabel');

function setStatus(message, type = 'idle') {
    statusLabel.textContent = message;
    statusDot.className = 'status-dot';
    if (type === 'warn') statusDot.classList.add('active');
    if (type === 'ok') statusDot.classList.add('saved');
    if (type === 'error') statusDot.classList.add('error');
}

function updateSaveBtn() {
    const count = pendingChanges.size;
    [btnSaveTop, btnSaveBottom].forEach(btn => {
        if (btn) btn.disabled = count === 0;
    });
    if (count > 0) {
        countBadge.textContent = count;
        countBadge.style.display = 'inline-block';
        setStatus(`${count} perubahan belum disimpan.`, 'warn');
    } else {
        countBadge.style.display = 'none';
        setStatus('Semua data tersimpan.', 'ok');
    }
}

function focusNextCell(current) {
    const inputs = Array.from(document.querySelectorAll('.grid-input'));
    const index = inputs.indexOf(current);
    if (index > -1 && index < inputs.length - 1) {
        inputs[index + 1].focus();
    }
}

function focusPrevCell(current) {
    const inputs = Array.from(document.querySelectorAll('.grid-input'));
    const index = inputs.indexOf(current);
    if (index > 0) {
        inputs[index - 1].focus();
    }
}

function focusVertical(current, direction) {
    const inputs = Array.from(document.querySelectorAll('.grid-input'));
    const index = inputs.indexOf(current);
    const cols = document.querySelectorAll('.col-header').length;
    if (direction === 'down' && index + cols < inputs.length) {
        inputs[index + cols].focus();
    }
    if (direction === 'up' && index - cols >= 0) {
        inputs[index - cols].focus();
    }
}

function showTooltip(el) {
    const val = el.value.trim().toUpperCase();
    if (!val) return;
    const guru = GURU_DATA[val];
    if (!guru) return;

    const tooltip = document.getElementById('guruTooltip');
    document.getElementById('ttName').textContent = guru.nama;
    document.getElementById('ttSub').textContent = guru.bidang_studi || 'Mata Pelajaran Umum';
    
    const rect = el.getBoundingClientRect();
    tooltip.style.left = (rect.left + window.scrollX) + 'px';
    tooltip.style.top = (rect.top + window.scrollY - 60) + 'px';
    tooltip.classList.add('visible');
}

function hideTooltip() {
    document.getElementById('guruTooltip').classList.remove('visible');
}

document.getElementById('btnSaveAll').addEventListener('click', async function() {
    this.disabled = true;
    const originalText = this.innerHTML;
    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
    
    const changes = Array.from(pendingChanges.values());
    
    try {
        const response = await fetch(SAVE_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ 
                changes,
                semester: SEMESTER,
                tahun_ajaran: TAHUN_AJARAN,
                csrf_token: document.querySelector('meta[name="csrf-token"]')?.content || ''
            })
        });
        
        const result = await response.json();
        if (result.success) {
            alert('Perubahan berhasil disimpan!');
            window.location.reload();
        } else {
            alert('Gagal menyimpan: ' + (result.message || 'Error tidak dikenal'));
            this.disabled = false;
            this.innerHTML = originalText;
        }
    } catch (e) {
        alert('Terjadi kesalahan jaringan.');
        this.disabled = false;
        this.innerHTML = originalText;
    }
});

if (btnSaveBottom) {
    btnSaveBottom.addEventListener('click', function() {
        document.getElementById('btnSaveAll').click();
    });
}

const searchGuru = document.getElementById('searchGuru');
if (searchGuru) {
    searchGuru.addEventListener('input', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#guruListBody tr').forEach(row => {
            const match = (row.dataset.kode + row.dataset.nama + row.dataset.bidang).includes(q);
            row.style.display = match ? '' : 'none';
        });
    });
}

document.querySelectorAll('.guru-row').forEach(row => {
    row.addEventListener('click', function(e) {
        if (e.target.closest('button')) return;
        const kode = row.querySelector('td:first-child .badge')?.textContent?.trim();
        if (kode) insertKode(kode);
    });
});
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>
