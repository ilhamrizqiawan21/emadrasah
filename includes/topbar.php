<!-- emadrasah/includes/topbar.php -->
<header class="em-topbar" id="emTopbar">
    <div class="em-topbar__left">
        <!-- Mobile sidebar toggle -->
        <button class="em-topbar__hamburger d-lg-none" id="topbarHamburger" title="Menu">
            <i class="fas fa-bars"></i>
        </button>

        <!-- Brand (visible only on mobile when sidebar is hidden) -->
        <div class="em-topbar__brand d-lg-none">
            <img src="<?php echo base_url('assets/images/logo.png'); ?>" alt="Logo" width="22" height="22">
            <span>e-Madrasah</span>
        </div>

        <!-- Page title / breadcrumb area (desktop) -->
        <div class="em-topbar__title d-none d-lg-flex">
            <span id="topbarPageTitle"><?php echo $page_title ?? 'Dashboard'; ?></span>
        </div>
    </div>

    <div class="em-topbar__right">
        <!-- Global Search -->
        <div class="em-topbar__search">
            <i class="fas fa-search em-topbar__search-icon"></i>
            <input
                type="text"
                id="globalSearch"
                class="form-control em-topbar__search-input"
                placeholder="Cari..."
                autocomplete="off"
            >
            <div id="searchResultDropdown" class="dropdown-menu shadow-lg border-0 w-100 mt-2 p-0" style="max-height: 400px; overflow-y: auto; border-radius: 12px;"></div>
        </div>

        <!-- Dark mode toggle -->
        <button class="em-topbar__icon-btn" id="darkModeToggle" title="Dark / Light Mode">
            <i class="fas fa-moon"></i>
        </button>

        <!-- Notifications (placeholder) -->
        <div class="dropdown d-none d-md-flex">
            <button class="em-topbar__icon-btn" data-bs-toggle="dropdown" title="Notifikasi">
                <i class="fas fa-bell"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-0 mt-2" style="width: 300px; border-radius: 12px; overflow: hidden;">
                <div class="p-3 bg-primary text-white">
                    <h6 class="mb-0">Notifikasi</h6>
                </div>
                <div class="p-4 text-center text-muted">
                    <i class="fas fa-bell-slash fa-2x mb-3 opacity-25"></i>
                    <p class="small mb-0">Tidak ada notifikasi baru.</p>
                </div>
            </div>
        </div>

        <!-- User dropdown -->
        <div class="dropdown">
            <button
                class="em-topbar__user-btn d-flex align-items-center gap-2"
                data-bs-toggle="dropdown"
                title="<?php echo $_SESSION['user_name'] ?? 'Admin'; ?>"
            >
                <div class="em-topbar__avatar">
                    <span><?php echo strtoupper(substr($_SESSION['user_name'] ?? 'A', 0, 1)); ?></span>
                </div>
                <span class="small fw-bold d-none d-md-inline em-topbar__username">
                    <?php echo $_SESSION['user_name'] ?? 'Admin'; ?>
                </span>
                <i class="fas fa-chevron-down opacity-50" style="font-size: 0.65rem;"></i>
            </button>
            <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-2 mt-2" style="width: 200px; border-radius: 12px;">
                <a class="dropdown-item rounded-8 py-2" href="<?php echo base_url('modules/users/profile.php'); ?>">
                    <i class="fas fa-user-circle me-2 opacity-50"></i> Profil Saya
                </a>
                <hr class="dropdown-divider">
                <a class="dropdown-item rounded-8 py-2 text-danger" href="<?php echo base_url('logout.php'); ?>">
                    <i class="fas fa-sign-out-alt me-2 opacity-50"></i> Keluar
                </a>
            </div>
        </div>
    </div>
</header>
