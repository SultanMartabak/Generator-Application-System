<?php use App\Filters\Rbac; ?>

<div class="d-none d-md-block">
    <div class="table-responsive rounded-4 shadow-sm border-0">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="text-center">No.</th>
                    <th><i class="bi bi-person-fill"></i> Username</th>
                    <th><i class="bi bi-card-heading"></i> Nama Lengkap</th>
                    <th><i class="bi bi-envelope-fill"></i> Email</th>
                    <th><i class="bi bi-shield-fill"></i> Roles</th>
                    <th>Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php $startNumber = (($pager->getCurrentPage('default') - 1) * $pager->getPerPage('default')) + 1; ?>
            <?php if (empty($users)): ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-5">Tidak ada user ditemukan.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($users as $user): ?>
                    <tr class="bg-white border-bottom">
                        <td class="text-center fw-bold text-secondary-emphasis">#<?= $startNumber++ ?></td>
                        <td class="fw-semibold text-primary-emphasis"><i class="bi bi-person-circle me-1"></i> <?= esc($user['username']) ?></td>
                        <td><?= esc($user['nama_lengkap']) ?></td>
                        <td><span class="badge bg-light text-dark border"><i class="bi bi-envelope"></i> <?= esc($user['email']) ?></span></td>
                        <td><span class="badge bg-info-subtle text-info-emphasis px-2 py-1 rounded-pill"> <?= esc($user['roles'] ?: '-') ?> </span></td>
                        <td><?= $user['is_active'] ? '<span class="badge bg-success-subtle text-success-emphasis px-3 py-2 rounded-pill">Aktif</span>' : '<span class="badge bg-danger-subtle text-danger-emphasis px-3 py-2 rounded-pill">Tidak Aktif</span>' ?></td>
                        <td class="text-center">
                            <?php
                            $rolesOfUser = !empty($user['role_ids']) ? explode(',', $user['role_ids']) : [];
                            $isTargetUserSuperAdmin = in_array(SUPER_ADMIN_ROLE_ID, $rolesOfUser);
                            $isSelf = (session()->get('user_id') == $user['id']);
                            $isCurrentUserSuperAdmin = (session()->get('role_id') == SUPER_ADMIN_ROLE_ID);
                            ?>
                            <div class="dropdown">
                              <button class="btn btn-gradient btn-sm dropdown-toggle px-3 py-2 fw-semibold rounded-pill shadow-sm border-0<?php if ($isTargetUserSuperAdmin && !$isCurrentUserSuperAdmin) echo ' disabled'; ?>" type="button" data-bs-toggle="dropdown" data-bs-offset="0,12" aria-expanded="false" style="background: linear-gradient(90deg,#0d6efd 0,#6610f2 100%); color: #fff;">
                                <i class="bi bi-gear"></i> Aksi
                              </button>
                              <ul class="dropdown-menu dropdown-menu-end animate__animated animate__fadeIn animate__faster rounded-3 shadow" style="min-width:160px; box-shadow:0 8px 32px 0 rgba(60,60,120,0.18),0 1.5px 4px 0 rgba(60,60,120,0.10);">
                                <?php if ($isSelf): ?>
                                  <li><a class="dropdown-item d-flex align-items-center gap-2 text-primary-emphasis" href="#" data-bs-toggle="modal" data-bs-target="#userModal" onclick='openEditUserModal(<?= json_encode($user) ?>, <?= json_encode(explode(", ", $user["roles"])) ?>, true)'><i class="bi bi-person-lines-fill"></i> Edit Profil</a></li>
                                <?php elseif ($isTargetUserSuperAdmin && !$isCurrentUserSuperAdmin): ?>
                                  <li><span class="dropdown-item text-muted d-flex align-items-center gap-2"><i class="bi bi-shield-lock"></i> Tidak diizinkan</span></li>
                                <?php else: ?>
                                  <?php if (Rbac::userCan('can_update', '/setting/users')): ?>
                                    <li><a class="dropdown-item d-flex align-items-center gap-2 text-warning-emphasis" href="#" data-bs-toggle="modal" data-bs-target="#userModal" onclick='openEditUserModal(<?= json_encode($user) ?>, <?= json_encode(explode(", ", $user["roles"])) ?>, false)'><i class="bi bi-pencil-square"></i> Edit</a></li>
                                  <?php endif; ?>
                                  <?php if (Rbac::userCan('can_delete', '/setting/users') && !$isSelf): ?>
                                    <li><a class="dropdown-item d-flex align-items-center gap-2 text-danger-emphasis" href="#" onclick="openDeleteModal(<?= $user['id'] ?>, '<?= esc($user['username']) ?>')"><i class="bi bi-trash"></i> Hapus</a></li>
                                  <?php endif; ?>
                                  <?php if (Rbac::userCan('can_reset_password', '/setting/users') && !$isSelf): ?>
                                    <li><a class="dropdown-item d-flex align-items-center gap-2 text-info-emphasis" href="#" data-bs-toggle="modal" data-bs-target="#resetPasswordModal" onclick="openResetPasswordModal(<?= $user['id'] ?>, '<?= esc($user['username']) ?>')"><i class="bi bi-key"></i> Reset Password</a></li>
                                  <?php endif; ?>
                                <?php endif; ?>
                              </ul>
                            </div>
                        </td>
                    </tr>
                <?php endforeach ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div> 

<div class="d-md-none">
    <?php $startNumber = (($pager->getCurrentPage('default') - 1) * $pager->getPerPage('default')) + 1; ?>
    <?php if (empty($users)): ?>
        <div class="alert alert-info text-center">Tidak ada user ditemukan.</div>
    <?php else: ?>
        <?php foreach ($users as $user): ?>
            <div class="card mb-3 shadow-sm border-0 rounded-4">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">No. <?= $startNumber++ ?></h6>
                    <h5 class="card-title text-primary-emphasis"><i class="bi bi-person-fill"></i> <?= esc($user['username']) ?></h5>
                    <p class="card-text mb-1"><strong>Nama Lengkap:</strong> <?= esc($user['nama_lengkap']) ?></p>
                    <p class="card-text mb-1"><strong>Email:</strong> <span class="badge bg-light text-dark border"><i class="bi bi-envelope"></i> <?= esc($user['email']) ?></span></p>
                    <p class="card-text mb-1"><strong>Roles:</strong> <span class="badge bg-info-subtle text-info-emphasis px-2 py-1 rounded-pill"> <?= esc($user['roles'] ?: '-') ?> </span></p>
                    <p class="card-text mb-3"><strong>Status:</strong> <?= $user['is_active'] ? '<span class="badge bg-success-subtle text-success-emphasis px-3 py-2 rounded-pill">Aktif</span>' : '<span class="badge bg-danger-subtle text-danger-emphasis px-3 py-2 rounded-pill">Tidak Aktif</span>' ?></p>
                    <div class="d-flex flex-wrap">
                        <?php
                        $rolesOfUser = !empty($user['role_ids']) ? explode(',', $user['role_ids']) : [];
                        $isTargetUserSuperAdmin = in_array(SUPER_ADMIN_ROLE_ID, $rolesOfUser);
                        $isSelf = (session()->get('user_id') == $user['id']);
                        $isCurrentUserSuperAdmin = (session()->get('role_id') == SUPER_ADMIN_ROLE_ID);
                        ?>
                        <div class="dropdown w-100">
                          <button class="btn btn-gradient btn-sm dropdown-toggle w-100 rounded-pill shadow-sm border-0<?php if ($isTargetUserSuperAdmin && !$isCurrentUserSuperAdmin) echo ' disabled'; ?>" type="button" data-bs-toggle="dropdown" data-bs-offset="0,12" aria-expanded="false" style="background: linear-gradient(90deg,#0d6efd 0,#6610f2 100%); color: #fff;">
                            <i class="bi bi-gear"></i> Aksi
                          </button>
                          <ul class="dropdown-menu w-100 animate__animated animate__fadeIn animate__faster rounded-3 shadow">
                            <?php if ($isSelf): ?>
                              <li><a class="dropdown-item d-flex align-items-center gap-2 text-primary-emphasis" href="#" data-bs-toggle="modal" data-bs-target="#userModal" onclick='openEditUserModal(<?= json_encode($user) ?>, <?= json_encode(explode(", ", $user["roles"])) ?>, true)'><i class="bi bi-person-lines-fill"></i> Edit Profil</a></li>
                            <?php elseif ($isTargetUserSuperAdmin && !$isCurrentUserSuperAdmin): ?>
                              <li><span class="dropdown-item text-muted d-flex align-items-center gap-2"><i class="bi bi-shield-lock"></i> Tidak diizinkan</span></li>
                            <?php else: ?>
                              <?php if (Rbac::userCan('can_update', '/setting/users')): ?>
                                <li><a class="dropdown-item d-flex align-items-center gap-2 text-warning-emphasis" href="#" data-bs-toggle="modal" data-bs-target="#userModal" onclick='openEditUserModal(<?= json_encode($user) ?>, <?= json_encode(explode(", ", $user["roles"])) ?>, false)'><i class="bi bi-pencil-square"></i> Edit</a></li>
                              <?php endif; ?>
                              <?php if (Rbac::userCan('can_delete', '/setting/users') && !$isSelf): ?>
                                <li><a class="dropdown-item d-flex align-items-center gap-2 text-danger-emphasis" href="#" onclick="openDeleteModal(<?= $user['id'] ?>, '<?= esc($user['username']) ?>')"><i class="bi bi-trash"></i> Hapus</a></li>
                              <?php endif; ?>
                              <?php if (Rbac::userCan('can_reset_password', '/setting/users') && !$isSelf): ?>
                                <li><a class="dropdown-item d-flex align-items-center gap-2 text-info-emphasis" href="#" data-bs-toggle="modal" data-bs-target="#resetPasswordModal" onclick="openResetPasswordModal(<?= $user['id'] ?>, '<?= esc($user['username']) ?>')"><i class="bi bi-key"></i> Reset Password</a></li>
                              <?php endif; ?>
                            <?php endif; ?>
                          </ul>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modern Pagination (for both desktop and mobile) -->
<div class="d-flex justify-content-center my-4">
    <?= $pager->links('default', 'modern_pagination') ?>
</div>