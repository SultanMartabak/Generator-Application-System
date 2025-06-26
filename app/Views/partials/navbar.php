<nav class="navbar navbar-dark bg-dark sticky-top" style="top:0;z-index:1055;">
    <div class="container-fluid d-flex align-items-center">
        <button class="btn btn-outline-light me-2" id="sidebarToggle" style="margin-right:16px;">
            <span class="navbar-toggler-icon"></span>
        </button>
        <span class="navbar-brand mb-0 h1"><?= htmlspecialchars($title ?? 'Dashboard', ENT_QUOTES, 'UTF-8') ?></span>
        <div class="ms-auto">
            <div class="dropdown">
                <button class="btn p-0 border-0 bg-transparent dropdown-toggle d-flex align-items-center" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="vertical-align: middle; cursor:pointer;">
                    <span class="me-2 text-white user-info">
                        <?= htmlspecialchars(session()->get('name') ?? 'Guest', ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars(session()->get('role_name') ?? 'N/A', ENT_QUOTES, 'UTF-8') ?>)
                    </span>
                    <img src="<?= htmlspecialchars(session()->get('avatar_url') ?? 'https://randomuser.me/api/portraits/men/32.jpg', ENT_QUOTES, 'UTF-8') ?>" alt="User" class="user-avatar avatar-img small-avatar">
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li><a class="dropdown-item" href="<?= site_url('password/change') ?>">Change Password</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="<?= site_url('login/logout') ?>">Logout</a></li>
                </ul>
            </div>           
         </div>
    </div>
</nav>
