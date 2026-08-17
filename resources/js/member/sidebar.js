export function initSidebar({layoutId, sidebarId, toggleBtnId, closeBtnId, overlayId,
    profileBtnId, dropdownId,
}) {
    const layout = document.getElementById(layoutId);
    const sidebar = document.getElementById(sidebarId);
    const toggleBtn = document.getElementById(toggleBtnId);
    const closeBtn = document.getElementById(closeBtnId);
    const overlay = document.getElementById(overlayId);
    const profileBtn = document.getElementById(profileBtnId);
    const dropdown = document.getElementById(dropdownId);
    if (!layout || !sidebar) return;
    const sidebarScroll = sidebar.querySelector('.akd-sidebar__scroll');

    if (sidebarScroll) {
        const userId = sidebar.dataset.userId;

        if (userId) {
            const SIDEBAR_SCROLL_KEY = `akd-sidebar-scroll:${userId}`;

            // Restore this user's previous sidebar position.
            const savedScroll = sessionStorage.getItem(SIDEBAR_SCROLL_KEY);

            if (savedScroll !== null) {
                requestAnimationFrame(() => {
                    sidebarScroll.scrollTop = parseInt(savedScroll, 10) || 0;
                });
            }

            // Save this user's sidebar position.
            sidebarScroll.addEventListener('scroll', () => {
                sessionStorage.setItem(
                    SIDEBAR_SCROLL_KEY,
                    String(sidebarScroll.scrollTop)
                );
            }, { passive: true });
        }
    }

    const openSidebar = () => {
        sidebar.classList.add('is-open');
        overlay?.classList.add('is-open');
        toggleBtn?.setAttribute('aria-expanded', 'true');
        document.body.style.overflow = 'hidden';
    };

    const closeSidebar = () => {
        sidebar.classList.remove('is-open');
        overlay?.classList.remove('is-open');
        toggleBtn?.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
    };

    toggleBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        sidebar.classList.contains('is-open') ? closeSidebar() : openSidebar();
    });

    closeBtn?.addEventListener('click', closeSidebar);
    overlay?.addEventListener('click', closeSidebar);

    window.addEventListener('resize', () => {
        if (window.innerWidth >= 1024 && sidebar.classList.contains('is-open')) {
            closeSidebar();
        }
    });

    // Profile dropdown
    // Profile dropdown
    if (profileBtn && dropdown) {
        profileBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = dropdown.classList.toggle('is-open');
            profileBtn.setAttribute('aria-expanded', String(isOpen));
        });

        document.addEventListener('click', (e) => {
            if (!profileBtn.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.remove('is-open');
                profileBtn.setAttribute('aria-expanded', 'false');
            }
        });
    }

    // Nested navigation groups — each branch's expand/collapse is independent.
    const expandButtons = sidebar.querySelectorAll('.akd-sidebar__expand-btn');
    expandButtons.forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const submenu = document.getElementById(btn.getAttribute('aria-controls'));
            if (!submenu) return;

            const isOpen = btn.getAttribute('aria-expanded') === 'true';
            btn.setAttribute('aria-expanded', String(!isOpen));
            submenu.classList.toggle('is-open', !isOpen);
        });
    });

    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        closeSidebar();
        dropdown?.classList.remove('is-open');
        profileBtn?.setAttribute('aria-expanded', 'false');
    });
}