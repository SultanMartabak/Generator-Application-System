<?php
$currentUrl = current_url();

// --- DEBUGGING START ---
// Uncomment baris di bawah ini untuk melihat isi variabel $sidebarMenus di halaman web
// echo '<pre>'; var_dump($sidebarMenus); echo '</pre>';

use App\Filters\Rbac;
// The MenuModel already filters menus based on permissions.
// The $sidebarMenus variable passed to this view contains only the menus
// the user is allowed to see. Therefore, we no longer need the canViewMenu() function.

function renderSidebarMenu($menus, $currentUrl, $parentKey = '') {
    foreach (($menus ?? []) as $idx => $menu) {
        $isActive = !empty($menu['url']) && site_url($menu['url']) === $currentUrl;
        $hasActiveChild = false;
        if (!empty($menu['children'])) {
            foreach ($menu['children'] as $child) {
                if (!empty($child['url']) && site_url($child['url']) === $currentUrl) {
                    $hasActiveChild = true;
                    break;
                }
            }
        }
        $collapseId = 'collapseMenu' . ($parentKey ? $parentKey . '_' : '') . $idx;

        if (!empty($menu['children'])) {
            // Parent menu with collapse
            echo '<a class="nav-link m-1 d-flex align-items-center justify-content-between'.($hasActiveChild ? ' active' : '').'" data-bs-toggle="collapse" href="#'.$collapseId.'" role="button" aria-expanded="'.($hasActiveChild ? 'true' : 'false').'" aria-controls="'.$collapseId.'" title="'.esc($menu['name']).'">';
            if ($menu['icon']) echo '<i class="'.esc($menu['icon']).' me-2"></i>';
            echo '<span class="sidebar-menu-text">'.esc($menu['name']).'</span>';
            echo '<i class="bi bi-chevron-down ms-auto"></i>';
            echo '</a>';
            echo '<div class="collapse'.($hasActiveChild ? ' show' : '').'" id="'.$collapseId.'">';
            echo '<nav class="nav flex-column ms-3">';
            renderSidebarMenu($menu['children'], $currentUrl, $collapseId);
            echo '</nav>';
            echo '</div>';
        } else {
            // Single menu
            echo '<a class="nav-link m-1 d-flex align-items-center'.($isActive ? ' active' : '').'" href="'.site_url($menu['url']).'" data-bs-toggle="tooltip" data-bs-placement="right" title="'.esc($menu['name']).'">';
            if ($menu['icon']) echo '<i class="'.esc($menu['icon']).' me-2"></i>';
            echo '<span class="sidebar-menu-text">'.esc($menu['name']).'</span>';
            echo '</a>';
        }
    }
}
?>
<div class="sidebar" id="sidebar">
    <div class="logo">
        <!-- <img src="<?= base_url('img/gas.png?v=1.0') ?>" alt="GAS Logo" class="logo-icon" id="sidebarLogo" data-bs-toggle="tooltip" data-bs-placement="right" title="GAS"> -->
        <span class="logo-text h6">Generator Application System</span>
    </div>
    <!-- User avatar example (letakkan di bawah logo jika ingin tampil di sidebar) -->

    <nav class="nav flex-column mt-3">
        <?php renderSidebarMenu($sidebarMenus ?? [], $currentUrl); ?>
    </nav>
</div>
<div class="sidebar-backdrop" id="sidebarBackdrop"></div>
