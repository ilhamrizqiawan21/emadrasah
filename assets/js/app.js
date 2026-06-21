/* ================================================================
   e-Madrasah · app.js  v4.1
   Satu sistem sidebar — em-sidebar only
   Changelog:
   - Unified confirm modal (showConfirmModal) into app.js
   - Sidebar toggle now adjusts topbar position
   ================================================================ */

(function () {
    'use strict';

    /* ── Konstanta ── */
    const LS_KEY          = 'em_sidebar_collapsed';
    const LS_DARK_MODE    = 'em_dark_mode';
    const BP              = 992;  /* desktop breakpoint */

    /* ── Helpers ── */
    const $   = (sel, ctx = document) => ctx.querySelector(sel);
    const $$  = (sel, ctx = document) => [...ctx.querySelectorAll(sel)];
    const isDesktop = () => window.innerWidth >= BP;

    /* ── Theme Management ── */
    function initTheme() {
        const isDark = localStorage.getItem(LS_DARK_MODE) === 'true';
        document.documentElement.setAttribute('data-bs-theme', isDark ? 'dark' : 'light');
        
        const btn = $('#darkModeToggle');
        if (btn) {
            updateThemeIcon(btn, isDark);
            btn.addEventListener('click', () => {
                const nowDark = document.documentElement.getAttribute('data-bs-theme') === 'light';
                document.documentElement.setAttribute('data-bs-theme', nowDark ? 'dark' : 'light');
                localStorage.setItem(LS_DARK_MODE, nowDark);
                updateThemeIcon(btn, nowDark);
            });
        }
        // Also update sidebar dark mode toggle if exists
        const sidebarDarkBtn = $('#darkModeToggleSidebar');
        if (sidebarDarkBtn) {
            updateThemeIcon(sidebarDarkBtn, isDark);
            sidebarDarkBtn.addEventListener('click', () => {
                const nowDark = document.documentElement.getAttribute('data-bs-theme') === 'light';
                document.documentElement.setAttribute('data-bs-theme', nowDark ? 'dark' : 'light');
                localStorage.setItem(LS_DARK_MODE, nowDark);
                updateThemeIcon(sidebarDarkBtn, nowDark);
                // Sync topbar icon
                const topbarBtn = $('#darkModeToggle');
                if (topbarBtn) updateThemeIcon(topbarBtn, nowDark);
            });
        }
    }

    function updateThemeIcon(btn, isDark) {
        const icon = btn.querySelector('i');
        if (icon) {
            icon.className = isDark ? 'fas fa-sun' : 'fas fa-moon';
        }
    }

    /* ── Global Search ── */
    function initGlobalSearch() {
        const input = $('#globalSearch');
        const dropdown = $('#searchResultDropdown');
        if (!input || !dropdown) return;

        let timeout = null;
        input.addEventListener('input', () => {
            clearTimeout(timeout);
            const q = input.value.trim();
            if (q.length < 2) {
                dropdown.classList.remove('show');
                return;
            }

            timeout = setTimeout(async () => {
                try {
                    const basePath = window.location.pathname.replace(/\/[^/]*$/, '');
                    const res = await fetch(`${window.location.origin}${basePath}/search_ajax.php?q=${encodeURIComponent(q)}`);
                    const data = await res.json();
                    
                    if (data.length > 0) {
                        dropdown.innerHTML = data.map(item => `
                            <a href="${item.url}" class="dropdown-item d-flex justify-content-between align-items-center py-2 px-3 border-bottom border-light">
                                <div>
                                    <div class="fw-bold small text-dark">${item.title}</div>
                                    <div class="text-muted" style="font-size: 0.7rem;">${item.category}</div>
                                </div>
                                <i class="fas fa-chevron-right small opacity-25"></i>
                            </a>
                        `).join('');
                        dropdown.classList.add('show');
                    } else {
                        dropdown.innerHTML = '<div class="p-3 text-center text-muted small">Tidak ada hasil ditemukan.</div>';
                        dropdown.classList.add('show');
                    }
                } catch (e) {
                    console.error('Search error:', e);
                }
            }, 300);
        });

        document.addEventListener('click', (e) => {
            if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.remove('show');
            }
        });
    }

    /* ── Dropzone (Drag & Drop) ── */
    function initDropzones() {
        $$('.em-dropzone').forEach(zone => {
            const input = zone.querySelector('input[type="file"]');
            const preview = zone.querySelector('.em-dropzone__preview');
            if (!input) return;

            zone.addEventListener('dragover', (e) => {
                e.preventDefault();
                zone.classList.add('is-dragover');
            });

            ['dragleave', 'dragend', 'drop'].forEach(type => {
                zone.addEventListener(type, () => zone.classList.remove('is-dragover'));
            });

            zone.addEventListener('drop', (e) => {
                e.preventDefault();
                if (e.dataTransfer.files.length) {
                    input.files = e.dataTransfer.files;
                    updatePreview(zone, input.files[0], preview);
                }
            });

            input.addEventListener('change', () => {
                if (input.files.length) {
                    updatePreview(zone, input.files[0], preview);
                }
            });
        });
    }

    function updatePreview(zone, file, previewEl) {
        zone.classList.add('has-file');
        if (previewEl) {
            previewEl.textContent = `File terpilih: ${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
        }
    }

    /* ================================================================
       1. Sidebar
       ================================================================ */
    function initSidebar() {
        const sidebar = $('#emSidebar');
        const overlay = $('#sidebarOverlay');
        const topbar  = $('#emTopbar');
        if (!sidebar) return;

        /* ── Restore collapsed state di desktop ── */
        if (isDesktop() && localStorage.getItem(LS_KEY) === 'true') {
            sidebar.classList.add('is-collapsed');
        }

        /* ── Update topbar position ── */
        updateTopbarPosition();

        /* ── Desktop toggle ── */
        const btnDesktop = $('#sidebarToggleDesktop');
        if (btnDesktop) {
            btnDesktop.addEventListener('click', () => {
                if (!isDesktop()) return;
                const nowCollapsed = sidebar.classList.toggle('is-collapsed');
                localStorage.setItem(LS_KEY, nowCollapsed);
                updateTopbarPosition();
                initTooltips();
            });
        }

        /* ── Mobile FAB toggle ── */
        const btnMobile = $('#sidebarToggleMobile');
        if (btnMobile) {
            btnMobile.addEventListener('click', () => {
                if (isDesktop()) return;
                sidebar.classList.contains('is-open') ? closeMobile() : openMobile();
            });
        }

        /* Topbar hamburger (mobile) */
        const btnHamburger = $('#topbarHamburger');
        if (btnHamburger) {
            btnHamburger.addEventListener('click', () => {
                if (isDesktop()) return;
                sidebar.classList.contains('is-open') ? closeMobile() : openMobile();
            });
        }

        /* ── Overlay click → tutup ── */
        if (overlay) {
            overlay.addEventListener('click', closeMobile);
        }

        /* ── Escape → tutup ── */
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && sidebar.classList.contains('is-open')) {
                closeMobile();
            }
        });

        /* ── Resize: bersihkan mobile state saat kembali ke desktop ── */
        window.addEventListener('resize', debounce(() => {
            if (isDesktop()) {
                sidebar.classList.remove('is-open');
                if (overlay) overlay.classList.remove('is-visible');
                document.body.style.overflow = '';
            }
            updateTopbarPosition();
        }, 150));

        /* ── Tutup sidebar mobile saat klik nav link ── */
        $$('.em-nav__link', sidebar).forEach(link => {
            link.addEventListener('click', () => {
                if (!isDesktop()) closeMobile();
            });
        });

        /* ── Sidebar Dropdowns ── */
        $$('.em-nav__dropdown-toggle', sidebar).forEach(toggle => {
            toggle.addEventListener('click', (e) => {
                e.preventDefault();
                const parent = toggle.closest('.em-nav__dropdown');
                if (parent) {
                    parent.classList.toggle('is-open');
                }
            });
        });

        function openMobile() {
            sidebar.classList.add('is-open');
            if (overlay) overlay.classList.add('is-visible');
            document.body.style.overflow = 'hidden';
        }

        function closeMobile() {
            sidebar.classList.remove('is-open');
            if (overlay) overlay.classList.remove('is-visible');
            document.body.style.overflow = '';
        }

        function updateTopbarPosition() {
            if (!topbar) return;
            if (isDesktop()) {
                const isCollapsed = sidebar.classList.contains('is-collapsed');
                topbar.style.left = isCollapsed
                    ? getComputedStyle(document.documentElement).getPropertyValue('--em-sidebar-collapsed-w').trim()
                    : getComputedStyle(document.documentElement).getPropertyValue('--em-sidebar-w').trim();
            } else {
                topbar.style.left = '0';
            }
        }
    }

    /* ================================================================
       2. Active nav link
       ================================================================ */
    function initActiveNav() {
        const currentPath = window.location.pathname;
        $$('.em-nav__link').forEach(link => {
            if (link.classList.contains('is-active')) {
                const parentDropdown = link.closest('.em-nav__dropdown');
                if (parentDropdown) parentDropdown.classList.add('is-open');
                return;
            }
            const href = link.getAttribute('href');
            if (href && href !== '#' && href !== '/' && currentPath.startsWith(href)) {
                link.classList.add('is-active');
                const parentDropdown = link.closest('.em-nav__dropdown');
                if (parentDropdown) parentDropdown.classList.add('is-open');
            }
        });
    }

    /* ================================================================
       3. Bootstrap Tooltips
       ================================================================ */
    let activeTooltips = [];
    function initTooltips() {
        if (typeof bootstrap === 'undefined') return;
        
        activeTooltips.forEach(t => t.dispose());
        activeTooltips = [];

        const sidebar = $('#emSidebar');
        const isCollapsed = sidebar && sidebar.classList.contains('is-collapsed');

        $$('[data-bs-toggle="tooltip"]').forEach(el => {
            const isSidebarLink = el.closest('.em-sidebar');
            if (isSidebarLink && !isCollapsed) return;

            const t = new bootstrap.Tooltip(el, { 
                trigger: 'hover',
                boundary: 'viewport'
            });
            activeTooltips.push(t);
        });
    }

    /* ================================================================
       4. Auto-dismiss alerts (5 detik)
       ================================================================ */
    function initAlerts() {
        $$('.em-alert:not(.alert-permanent)').forEach(el => {
            setTimeout(() => {
                if (typeof bootstrap !== 'undefined') {
                    try { 
                        const bsAlert = bootstrap.Alert.getOrCreateInstance(el);
                        if (bsAlert) bsAlert.close();
                    } catch (_) {}
                } else {
                    el.style.transition = 'opacity 0.4s, transform 0.4s';
                    el.style.opacity = '0';
                    el.style.transform = 'translateY(-6px)';
                    setTimeout(() => el.remove(), 420);
                }
            }, 5000);
        });
    }

    /* ================================================================
       5. Form validation (real-time + submit)
       ================================================================ */
    function initForms() {
        $$('form').forEach(form => {
            $$('[required]', form).forEach(field => {
                field.addEventListener('blur', () => validateField(field));
                field.addEventListener('input', () => {
                    if (field.classList.contains('is-invalid')) validateField(field);
                });
            });

            form.addEventListener('submit', e => {
                let valid = true;
                $$('[required]', form).forEach(field => {
                    if (!validateField(field)) valid = false;
                });
                if (!valid) {
                    e.preventDefault();
                    showToast('Harap lengkapi semua field yang wajib diisi.', 'danger');
                    const first = form.querySelector('.is-invalid');
                    if (first) first.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
        });
    }

    function validateField(field) {
        const ok = field.value.trim() !== '';
        field.classList.toggle('is-invalid', !ok);
        field.classList.toggle('is-valid',   ok);
        return ok;
    }

    /* ================================================================
       6. showConfirmModal — unified confirmation modal
       ================================================================ */
    window.showConfirmModal = function (message, callback) {
        /* Remove old modal if exists */
        $('#em-confirm-modal')?.remove();

        const wrap = document.createElement('div');
        wrap.id = 'em-confirm-modal';
        wrap.innerHTML = `
            <div class="modal fade" tabindex="-1" id="emConfirmModalBs">
                <div class="modal-dialog modal-dialog-centered modal-sm">
                    <div class="modal-content border-0 shadow-lg" style="border-radius:16px;overflow:hidden;">
                        <div class="modal-body p-4 text-center">
                            <div style="width:54px;height:54px;border-radius:50%;background:#fee2e2;
                                        display:flex;align-items:center;justify-content:center;
                                        margin:0 auto 14px;">
                                <i class="fas fa-exclamation-triangle text-danger" style="font-size:1.1rem;"></i>
                            </div>
                            <h6 class="fw-bold mb-1" style="font-size:0.95rem;">Konfirmasi Aksi</h6>
                            <p class="text-muted mb-0" style="font-size:0.82rem;">${message}</p>
                        </div>
                        <div class="modal-footer border-0 pt-0 pb-3 px-4 justify-content-center gap-2">
                            <button class="btn btn-outline-secondary btn-sm px-4"
                                    data-bs-dismiss="modal">Batal</button>
                            <button class="btn btn-danger btn-sm px-4" id="emConfirmOk">
                                Ya, Lanjutkan
                            </button>
                        </div>
                    </div>
                </div>
            </div>`;
        document.body.appendChild(wrap);

        const bsModal = new bootstrap.Modal($('#emConfirmModalBs'));
        bsModal.show();

        $('#emConfirmOk').addEventListener('click', () => {
            bsModal.hide();
            if (typeof callback === 'function') callback();
        });

        /* Clean up DOM after modal hides */
        $('#emConfirmModalBs').addEventListener('hidden.bs.modal', () => {
            wrap.remove();
        });
    };

    /* ================================================================
       7. showToast — toast notifikasi global
       ================================================================ */
    window.showToast = function (message, type = 'success') {
        let container = $('.toast-container.em-toast-container');
        if (!container) {
            container = Object.assign(document.createElement('div'), {
                className: 'toast-container em-toast-container position-fixed bottom-0 end-0 p-3',
            });
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }

        const icons = {
            success : 'fa-circle-check',
            danger  : 'fa-circle-xmark',
            warning : 'fa-triangle-exclamation',
            info    : 'fa-circle-info',
        };
        const icon = icons[type] ?? 'fa-circle-info';

        const wrap = document.createElement('div');
        wrap.innerHTML = `
            <div class="toast align-items-center border-0 shadow"
                 role="alert" data-bs-autohide="true" data-bs-delay="4500"
                 style="border-radius:12px;min-width:270px;overflow:hidden;">
                <div class="d-flex align-items-center gap-2 p-3">
                    <i class="fas ${icon} text-${type}" style="font-size:1rem;flex-shrink:0;"></i>
                    <div class="flex-grow-1 small fw-500">${message}</div>
                    <button type="button" class="btn-close btn-close-sm ms-auto"
                            data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>`;
        container.appendChild(wrap);

        const toast = new bootstrap.Toast(wrap.firstElementChild);
        toast.show();
        wrap.firstElementChild.addEventListener('hidden.bs.toast', () => wrap.remove());
    };

    /* ================================================================
       8. confirmDelete — convenience wrapper untuk delete form POST
       ================================================================ */
    window.confirmDelete = function (url, name = '') {
        const displayName = name
            ? `<strong>${escHtml(name)}</strong>`
            : 'data ini';

        showConfirmModal(
            `Hapus ${displayName}? Tindakan ini tidak dapat dibatalkan.`,
            () => submitDelete(url)
        );
    };

    function submitDelete(url) {
        const f      = document.createElement('form');
        f.method     = 'POST';
        f.action     = url;
        const csrf   = inp('csrf_token', $('meta[name="csrf-token"]')?.content ?? '');
        f.append(csrf);
        document.body.appendChild(f);
        f.submit();
    }

    function inp(name, value) {
        return Object.assign(document.createElement('input'), {
            type: 'hidden', name, value
        });
    }

    function escHtml(str) {
        return str.replace(/[&<>"']/g, m => ({
            '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
        }[m]));
    }

    /* ================================================================
       9. Fade-in animasi konten utama
       ================================================================ */
    function initFadeIn() {
        const main = $('#emMain') ?? $('main');
        if (main) main.classList.add('fade-in');
    }

    /* ================================================================
       Utility: debounce
       ================================================================ */
    function debounce(fn, delay) {
        let t;
        return (...args) => {
            clearTimeout(t);
            t = setTimeout(() => fn(...args), delay);
        };
    }

    /* ================================================================
       Init
       ================================================================ */
    document.addEventListener('DOMContentLoaded', () => {
        initTheme();
        initGlobalSearch();
        initDropzones();
        initSidebar();
        initActiveNav();
        initTooltips();
        initAlerts();
        initForms();
        initFadeIn();
    });

})();
