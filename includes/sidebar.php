<!-- emadrasah/includes/sidebar.php -->
<aside class="em-sidebar" id="emSidebar">
    <div class="em-sidebar__header">
        <div class="em-brand">
            <div class="em-brand__logo">
                <img src="<?php echo base_url('assets/images/logo.png'); ?>" alt="Logo" width="24" height="24">
            </div>
            <div class="em-brand__text">
                <span class="em-brand__title">e-Madrasah</span>
                <span class="em-brand__sub">MTs Al-Ihsan Batujajar</span>
            </div>
        </div>
        <button class="em-sidebar-toggle" id="sidebarToggleDesktop">
            <i class="fas fa-chevron-left"></i>
        </button>
    </div>

    <div class="em-sidebar__inner">
        <nav class="em-nav">
            <div class="em-nav__group">
                <span class="em-nav__label">Menu Utama</span>
                <a href="<?php echo base_url('index.php'); ?>" class="em-nav__link <?php echo (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'is-active' : ''; ?>">
                    <span class="em-nav__icon"><i class="fas fa-gauge-high"></i></span>
                    <span class="em-nav__text">Dashboard</span>
                </a>
            </div>

            <div class="em-nav__group">
                <span class="em-nav__label">Master Data</span>
                <a href="<?php echo base_url('modules/guru/index.php'); ?>" class="em-nav__link <?php echo (strpos($_SERVER['PHP_SELF'], '/modules/guru/') !== false) ? 'is-active' : ''; ?>">
                    <span class="em-nav__icon"><i class="fas fa-chalkboard-user"></i></span>
                    <span class="em-nav__text">Data Guru</span>
                </a>
                <a href="<?php echo base_url('modules/kelas/index.php'); ?>" class="em-nav__link <?php echo (strpos($_SERVER['PHP_SELF'], '/modules/kelas/') !== false) ? 'is-active' : ''; ?>">
                    <span class="em-nav__icon"><i class="fas fa-door-open"></i></span>
                    <span class="em-nav__text">Data Kelas</span>
                </a>
                <a href="<?php echo base_url('modules/mapel/index.php'); ?>" class="em-nav__link <?php echo (strpos($_SERVER['PHP_SELF'], '/modules/mapel/') !== false) ? 'is-active' : ''; ?>">
                    <span class="em-nav__icon"><i class="fas fa-book"></i></span>
                    <span class="em-nav__text">Mata Pelajaran</span>
                </a>
                <a href="<?php echo base_url('modules/tahun_pelajaran/index.php'); ?>" class="em-nav__link <?php echo (strpos($_SERVER['PHP_SELF'], '/modules/tahun_pelajaran/') !== false) ? 'is-active' : ''; ?>">
                    <span class="em-nav__icon"><i class="fas fa-calendar-alt"></i></span>
                    <span class="em-nav__text">Tahun Pelajaran</span>
                </a>
                <a href="<?php echo base_url('modules/jam_pelajaran/index.php'); ?>" class="em-nav__link <?php echo (strpos($_SERVER['PHP_SELF'], '/modules/jam_pelajaran/') !== false) ? 'is-active' : ''; ?>">
                    <span class="em-nav__icon"><i class="fas fa-clock"></i></span>
                    <span class="em-nav__text">Jam Pelajaran</span>
                </a>
            </div>

            <div class="em-nav__group">
                <span class="em-nav__label">Administrasi</span>
                <div class="em-nav__dropdown <?php echo (strpos($_SERVER['PHP_SELF'], '/modules/surat_masuk/') !== false || strpos($_SERVER['PHP_SELF'], '/modules/surat_keluar/') !== false || strpos($_SERVER['PHP_SELF'], '/modules/template_surat/') !== false) ? 'is-open' : ''; ?>" id="dropdownSurat">
                    <a href="javascript:void(0)" class="em-nav__dropdown-toggle" style="text-decoration: none;">
                        <span class="em-nav__icon"><i class="fas fa-mail-bulk"></i></span>
                        <span class="em-nav__text">Persuratan</span>
                        <i class="fas fa-chevron-down em-dropdown-icon"></i>
                    </a>
                    <div class="em-nav__dropdown-menu">
                        <a href="<?php echo base_url('modules/surat_masuk/index.php'); ?>" class="em-nav__link em-nav__link--sub <?php echo (strpos($_SERVER['PHP_SELF'], '/modules/surat_masuk/') !== false) ? 'is-active' : ''; ?>">Surat Masuk</a>
                        <a href="<?php echo base_url('modules/surat_keluar/index.php'); ?>" class="em-nav__link em-nav__link--sub <?php echo (strpos($_SERVER['PHP_SELF'], '/modules/surat_keluar/') !== false) ? 'is-active' : ''; ?>">Surat Keluar</a>
                        <a href="<?php echo base_url('modules/template_surat/index.php'); ?>" class="em-nav__link em-nav__link--sub <?php echo (strpos($_SERVER['PHP_SELF'], '/modules/template_surat/') !== false) ? 'is-active' : ''; ?>">Template Surat</a>
                    </div>
                </div>
                <a href="<?php echo base_url('modules/users/index.php'); ?>" class="em-nav__link <?php echo (strpos($_SERVER['PHP_SELF'], '/modules/users/') !== false) ? 'is-active' : ''; ?>">
                    <span class="em-nav__icon"><i class="fas fa-user-cog"></i></span>
                    <span class="em-nav__text">Manajemen User</span>
                </a>
                <a href="<?php echo base_url('modules/tasks/index.php'); ?>" class="em-nav__link <?php echo (strpos($_SERVER['PHP_SELF'], '/modules/tasks/') !== false) ? 'is-active' : ''; ?>">
                    <span class="em-nav__icon"><i class="fas fa-tasks"></i></span>
                    <span class="em-nav__text">Tugas TU</span>
                </a>
                <a href="<?php echo base_url('modules/sarana/index.php'); ?>" class="em-nav__link <?php echo (strpos($_SERVER['PHP_SELF'], '/modules/sarana/') !== false) ? 'is-active' : ''; ?>">
                    <span class="em-nav__icon"><i class="fas fa-boxes-stacked"></i></span>
                    <span class="em-nav__text">Sarana Prasarana</span>
                </a>
                <a href="<?php echo base_url('modules/kategori_sarana/index.php'); ?>" class="em-nav__link <?php echo (strpos($_SERVER['PHP_SELF'], '/modules/kategori_sarana/') !== false) ? 'is-active' : ''; ?>">
                    <span class="em-nav__icon"><i class="fas fa-tags"></i></span>
                    <span class="em-nav__text">Kategori Sarana</span>
                </a>
            </div>

            <div class="em-nav__group">
                <span class="em-nav__label">Akademik</span>
                <a href="<?php echo base_url('modules/absensi/index.php'); ?>" class="em-nav__link <?php echo (strpos($_SERVER['PHP_SELF'], '/modules/absensi/') !== false) ? 'is-active' : ''; ?>">
                    <span class="em-nav__icon"><i class="fas fa-fingerprint"></i></span>
                    <span class="em-nav__text">Absensi Guru</span>
                </a>
                <a href="<?php echo base_url('modules/jadwal/index.php'); ?>" class="em-nav__link <?php echo (strpos($_SERVER['PHP_SELF'], '/modules/jadwal/') !== false) ? 'is-active' : ''; ?>">
                    <span class="em-nav__icon"><i class="fas fa-table-cells"></i></span>
                    <span class="em-nav__text">Jadwal Pelajaran</span>
                </a>
                <a href="<?php echo base_url('modules/buku_induk/index.php'); ?>" class="em-nav__link <?php echo (strpos($_SERVER['PHP_SELF'], '/modules/buku_induk/') !== false || strpos($_SERVER['PHP_SELF'], '/modules/siswa/') !== false) ? 'is-active' : ''; ?>">
                    <span class="em-nav__icon"><i class="fas fa-user-graduate"></i></span>
                    <span class="em-nav__text">Buku Induk</span>
                </a>
                <a href="<?php echo base_url('modules/raport/index.php'); ?>" class="em-nav__link <?php echo (strpos($_SERVER['PHP_SELF'], '/modules/raport/') !== false) ? 'is-active' : ''; ?>">
                    <span class="em-nav__icon"><i class="fas fa-file-invoice"></i></span>
                    <span class="em-nav__text">Nilai Raport</span>
                </a>
                <a href="<?php echo base_url('modules/arsip/index.php'); ?>" class="em-nav__link <?php echo (strpos($_SERVER['PHP_SELF'], '/modules/arsip/') !== false) ? 'is-active' : ''; ?>">
                    <span class="em-nav__icon"><i class="fas fa-archive"></i></span>
                    <span class="em-nav__text">Arsip Akademik</span>
                </a>
            </div>
        </nav>
    </div>

    <div class="em-sidebar__footer">
        <div class="em-user-info">
            <div class="em-user-avatar">
                <span><?php echo strtoupper(substr($_SESSION['user_name'] ?? 'A', 0, 1)); ?></span>
            </div>
            <div class="em-user-details">
                <span class="em-user-name"><?php echo $_SESSION['user_name'] ?? 'Admin'; ?></span>
                <span class="em-user-role"><?php echo $_SESSION['user_role'] ?? 'Super Admin'; ?></span>
            </div>
            <div class="d-flex gap-1 ms-auto align-items-center">
                <button class="em-logout-btn" id="darkModeToggle" title="Toggle Dark Mode">
                    <i class="fas fa-moon"></i>
                </button>
                <a href="<?php echo base_url('logout.php'); ?>" class="em-logout-btn" title="Logout" onclick="return confirm('Yakin ingin keluar?')">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
        <div class="em-sidebar__version">e-Madrasah v1.1</div>
    </div>
</aside>

<button class="em-mobile-menu-btn d-lg-none" id="sidebarToggleMobile">
    <i class="fas fa-bars"></i>
</button>