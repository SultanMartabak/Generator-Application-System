let submenuToggleClicked = false;

const sidebar = document.getElementById('sidebar');
const mainContent = document.getElementById('mainContent');
const sidebarToggle = document.getElementById('sidebarToggle');
const sidebarBackdrop = document.getElementById('sidebarBackdrop');

function isMobile() {
    return window.innerWidth <= 991.98;
}

function closeSidebarMobile() {
    if (sidebar) {
        sidebar.classList.remove('show');
        sidebar.classList.add('collapsed');
    }
    if (sidebarBackdrop) {
        sidebarBackdrop.classList.remove('show');
    }
}

function openSidebarMobile() {
    if (sidebar) {
        sidebar.style.top = '56px'; // Position below navbar
        sidebar.style.height = 'calc(100vh - 56px)'; // Adjust height
        sidebar.classList.add('show');
        sidebar.classList.remove('collapsed');
    }
    if (sidebarBackdrop) {
        sidebarBackdrop.classList.add('show');
    }
    // Add class to main content wrapper to shift content
    const mainContentWrapper = document.querySelector('.flex-grow-1.d-flex.flex-column');
    if (mainContentWrapper) {
        mainContentWrapper.classList.add('sidebar-open');
    }
}

function saveSidebarState(isCollapsed) {
    try {
        localStorage.setItem('sidebarCollapsed', isCollapsed ? 'true' : 'false');
    } catch (e) {
        // ignore
    }
}

function loadSidebarState() {
    try {
        return localStorage.getItem('sidebarCollapsed') === 'true';
    } catch (e) {
        return false;
    }
}

// Toggle for both desktop and mobile
document.querySelectorAll('#sidebarToggle').forEach(btn => {
    btn.addEventListener('click', () => {
        if (isMobile()) {
            if (sidebar.classList.contains('show')) {
                closeSidebarMobile();
            } else {
                openSidebarMobile();
            }
        } else {
            sidebar.classList.toggle('collapsed');
            if (mainContent) mainContent.classList.toggle('collapsed');
            updateSidebarTooltips();
            saveSidebarState(sidebar.classList.contains('collapsed'));
        }
    });
});

if (sidebarBackdrop) {
    sidebarBackdrop.addEventListener('click', closeSidebarMobile);
}

// Responsive: close sidebar on resize to mobile, reset on desktop
window.addEventListener('resize', () => {
    if (isMobile()) {
        closeSidebarMobile();
        if (mainContent) mainContent.classList.remove('collapsed');
    } else {
        const collapsed = loadSidebarState();
        if (sidebar) {
            sidebar.classList.remove('show');
            if (collapsed) {
                sidebar.classList.add('collapsed');
            } else {
                sidebar.classList.remove('collapsed');
            }
        }
        if (sidebarBackdrop) sidebarBackdrop.classList.remove('show');
        if (mainContent) {
            if (collapsed) {
                mainContent.classList.add('collapsed');
            } else {
                mainContent.classList.remove('collapsed');
            }
        }
    }
    updateSidebarTooltips();
});

// Tooltip for logo and menu when sidebar collapsed
function updateSidebarTooltips() {
    const isCollapsed = sidebar.classList.contains('collapsed');
    // Logo tooltip
    const logo = document.getElementById('sidebarLogo');
    if (logo) {
        if (isCollapsed) {
            if (!logo._bsTooltip) {
                logo._bsTooltip = new bootstrap.Tooltip(logo);
            }
        } else {
            if (logo._bsTooltip) {
                logo._bsTooltip.dispose();
                logo._bsTooltip = null;
            }
        }
    }
    // Nav-link tooltips & hide menu text when collapsed
    document.querySelectorAll('#sidebar .nav-link').forEach(link => {
        const menuText = link.querySelector('.sidebar-menu-text');
        if (isCollapsed) {
            if (!link._bsTooltip) {
                link._bsTooltip = new bootstrap.Tooltip(link);
            }
            if (menuText) menuText.style.display = 'none';
        } else {
            if (link._bsTooltip) {
                link._bsTooltip.dispose();
                link._bsTooltip = null;
            }
            if (menuText) menuText.style.display = '';
        }
    });
}

// Call on load
window.addEventListener('DOMContentLoaded', () => {
    // Load sidebar state from localStorage
    const collapsed = loadSidebarState();
    if (collapsed) {
        sidebar.classList.add('collapsed');
        if (mainContent) mainContent.classList.add('collapsed');
    } else {
        sidebar.classList.remove('collapsed');
        if (mainContent) mainContent.classList.remove('collapsed');
    }
    updateSidebarTooltips();

    // Expand submenu of active menu item
    const activeLink = document.querySelector('#sidebar .nav-link.active[data-bs-toggle="collapse"]');
    if (activeLink) {
        const targetId = activeLink.getAttribute('href');
        if (targetId) {
            const collapseElement = document.querySelector(targetId);
            if (collapseElement && !collapseElement.classList.contains('show')) {
                collapseElement.classList.add('show');
                activeLink.setAttribute('aria-expanded', 'true');
            }
        }
    }

    // Initialize Bootstrap dropdowns manually
    document.querySelectorAll('.dropdown').forEach(dropdownEl => {
        const dropdownToggleEl = dropdownEl.querySelector('.dropdown-toggle');
        const dropdownMenuEl = dropdownEl.querySelector('.dropdown-menu');
        const dropdown = new bootstrap.Dropdown(dropdownToggleEl);

        let hideTimeout;

        // Show dropdown on mouseenter of dropdown container
        dropdownEl.addEventListener('mouseenter', () => {
            if (hideTimeout) {
                clearTimeout(hideTimeout);
                hideTimeout = null;
            }
            dropdown.show();
        });

        // Hide dropdown on mouseleave of dropdown container with delay
        dropdownEl.addEventListener('mouseleave', () => {
            hideTimeout = setTimeout(() => {
                dropdown.hide();
            }, 200);
        });
    });

    document.querySelectorAll('#sidebar .nav-link[data-bs-toggle="collapse"]').forEach(link => {
        link.addEventListener('click', function(e) {
            if (sidebar && sidebar.classList.contains('collapsed')) {
                // If sidebar is collapsed, allow toggling submenu but prevent sidebar expanding
                e.stopPropagation();
                // Do not prevent default to allow collapse toggle

                // Set flag to prevent immediate submenu closing on transitionend
                submenuToggleClicked = true;
                setTimeout(() => {
                    submenuToggleClicked = false;
                }, 100);
            }
        });
    });
});

// Update tooltip on resize
window.addEventListener('resize', updateSidebarTooltips);

/*
// Commented out to prevent submenu from closing immediately on sidebar transition
sidebar && sidebar.addEventListener('transitionend', function() {
    if (sidebar.classList.contains('collapsed')) {
        if (submenuToggleClicked) {
            // Prevent closing submenu immediately after toggle click
            return;
        }
        if (window.bootstrap && window.bootstrap.Collapse) {
            document.querySelectorAll('#sidebar .collapse.show').forEach(collapse => {
                bootstrap.Collapse.getOrCreateInstance(collapse).hide();
            });
        }
    }
});
*/
