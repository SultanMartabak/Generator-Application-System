<?= $this->extend('partials/main_layout') ?>

<?= $this->section('content') ?>
<?php
use App\Filters\Rbac;
$canCreate = Rbac::userCan('can_create', '/setting/roles');
$canUpdate = Rbac::userCan('can_update', '/setting/roles');
$canDelete = Rbac::userCan('can_delete', '/setting/roles');
// // Tambahkan ini untuk debugging
// echo '<pre>';
// echo 'canCreate: ' . ($canCreate ? 'true' : 'false') . '<br>';
// echo 'canUpdate: ' . ($canUpdate ? 'true' : 'false') . '<br>';
// echo 'canDelete: ' . ($canDelete ? 'true' : 'false') . '<br>';
// echo '</pre>';
?>
<div class="container mt-5">
    <h3>Daftar Roles</h3>
     <?php if ($canCreate): ?>
        <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#roleModal" onclick="openAddModal()">Tambah Role</button>
    <?php endif; ?>
    <?php if (session()->get('role_id') == SUPER_ADMIN_ROLE_ID): ?>
        <a href="<?= site_url('setting/roles/trash') ?>" class="btn btn-outline-secondary mb-3"><i class="bi bi-trash"></i> Lihat Role Dihapus</a>
    <?php endif; ?>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Deskripsi</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($roles as $role): ?>
            <tr>
                <td><?= esc($role['name']) ?></td>
                <td><?= esc($role['description']) ?></td>
                <td>
                    <?php if ($role['id'] == SUPER_ADMIN_ROLE_ID): ?>
                        <?php // Hanya Super Admin yang sedang login yang bisa melihat tombol ini ?>
                        <?php if (session()->get('role_id') == SUPER_ADMIN_ROLE_ID): ?>
                            <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#roleMenuModal" onclick='openRoleMenuModal(<?= esc($role['id']) ?>, "<?= esc($role['name']) ?>")'>Kelola Menu</button>
                        <?php else: ?>
                            <span class="text-muted">Tidak dapat diubah</span>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php if (Rbac::userCan('can_update', '/setting/roles')): ?>
                            <button class="btn btn-warning btn-sm me-1" data-bs-toggle="modal" data-bs-target="#roleModal" onclick='openEditModal(<?= json_encode($role) ?>)'>Edit</button>
                            <button class="btn btn-info btn-sm" onclick='openRoleMenuModal(<?= esc($role['id']) ?>, "<?= esc($role['name'], 'js') ?>")'>Kelola Menu</button>
                        <?php endif; ?>
                        <?php if (Rbac::userCan('can_delete', '/setting/roles')): ?>
                            <button class="btn btn-danger btn-sm" onclick="openDeleteRoleModal(<?= $role['id'] ?>, '<?= esc($role['name'], 'js') ?>')">Hapus</button>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="roleModal" tabindex="-1" aria-labelledby="roleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form class="modal-content" method="post" action="<?= site_url('setting/roles/save') ?>">
      <?= csrf_field() ?>
      <div class="modal-header">
        <h5 class="modal-title" id="roleModalLabel">Tambah/Edit Role</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="role_id">
        <div class="mb-3">
            <label class="form-label">Nama Role</label>
            <input type="text" name="name" id="role_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Deskripsi</label>
            <textarea name="description" id="role_description" class="form-control"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Simpan</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Delete Role -->
<div class="modal fade" id="deleteRoleModal" tabindex="-1" aria-labelledby="deleteRoleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="deleteRoleForm" class="modal-content" method="post">
      <?= csrf_field() ?>
      <div class="modal-header">
        <h5 class="modal-title" id="deleteRoleModalLabel">Konfirmasi Hapus Role</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Anda yakin ingin menghapus role <strong id="delete_role_name_span"></strong>?</p>
        <p class="text-warning small">Role ini hanya akan dipindahkan ke "trash" dan dapat dipulihkan nanti oleh Super Admin.</p>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-danger">Ya, Hapus Role</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Role Menu -->
<div class="modal fade" id="roleMenuModal" tabindex="-1" aria-labelledby="roleMenuModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" method="post" action="<?= site_url('setting/roles/savemenu') ?>">
      <?= csrf_field() ?>
      <div class="modal-header">
        <h5 class="modal-title" id="roleMenuModalLabel">Kelola Akses Menu untuk Role: <span id="roleNameForMenuModal"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="role_id_for_menu" id="role_menu_role_id">
        <p>Pilih menu yang dapat diakses oleh role ini:</p>
        <?php
        // Helper function to build a hierarchical menu tree from a flat array
        function buildRoleMenuTree(array $elements, $parentId = null): array
        {
            $branch = [];
            foreach ($elements as $element) {
                // Normalize parent_id 0 to null for consistency
                $elementParentId = ($element['parent_id'] == 0) ? null : $element['parent_id'];

                if ($elementParentId === $parentId) {
                    $children = buildRoleMenuTree($elements, $element['id']);
                    if ($children) {
                        $element['children'] = $children;
                    }
                    $branch[] = $element;
                }
            }
            return $branch;
        }

        // Helper function to render menu rows recursively
        function renderRoleMenuRow($menu, $level = 0) {
            $indent = str_repeat('&mdash; ', $level);
            $isParent = !empty($menu['children']);
            ?>
            <tr data-menu-id="<?= esc($menu['id']) ?>" data-menu-url="<?= esc($menu['url']) ?>">
                <td><?= $indent . esc($menu['name']) ?></td>
                <td><?= esc($menu['url']) ?></td>
                <td>
                    <input type="checkbox" name="menus[<?= esc($menu['id']) ?>][can_view]" value="1" id="role_menu_<?= esc($menu['id']) ?>_view">
                </td>
                <td>
                    <input type="checkbox" name="menus[<?= esc($menu['id']) ?>][can_create]" value="1" id="role_menu_<?= esc($menu['id']) ?>_create">
                </td>
                <td>
                    <input type="checkbox" name="menus[<?= esc($menu['id']) ?>][can_update]" value="1" id="role_menu_<?= esc($menu['id']) ?>_update">
                </td>
                <td>
                    <input type="checkbox" name="menus[<?= esc($menu['id']) ?>][can_delete]" value="1" id="role_menu_<?= esc($menu['id']) ?>_delete">
                </td>
                <td>
                    <?php if ($menu['url'] === '/setting/users'): ?>
                        <input type="checkbox" name="menus[<?= esc($menu['id']) ?>][can_reset_password]" value="1" id="role_menu_<?= esc($menu['id']) ?>_reset_password">
                    <?php endif; ?>
                </td>
            </tr>
            <?php
            if (!empty($menu['children'])) {
                foreach ($menu['children'] as $child) {
                    renderRoleMenuRow($child, $level + 1);
                }
            }
        }

        $menuTree = buildRoleMenuTree($menus); // Build the tree from the flat $menus array
        ?>
        <table class="table table-bordered table-striped" id="roleMenuPermissionsTable">
            <thead>
                <tr>
                    <th>Nama Menu</th>
                    <th>URL</th>
                    <th>View</th>
                    <th>Create</th>
                    <th>Update</th>
                    <th>Delete</th>
                    <th>Reset Password</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($menuTree as $menu): ?>
                    <?php renderRoleMenuRow($menu); ?>
                <?php endforeach ?>
            </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Simpan Perubahan Menu</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
      </div>
    </form>
  </div>
</div>

<script>
function openAddModal() {
    document.getElementById('roleModalLabel').innerText = 'Tambah Role';
    document.getElementById('role_id').value = '';
    document.getElementById('role_name').value = '';
    document.getElementById('role_description').value = '';
}
function openEditModal(role) {
    document.getElementById('roleModalLabel').innerText = 'Edit Role';
    document.getElementById('role_id').value = role.id;
    document.getElementById('role_name').value = role.name;
    document.getElementById('role_description').value = role.description;
}

function openDeleteRoleModal(roleId, roleName) {
    const form = document.getElementById('deleteRoleForm');
    form.action = `<?= site_url('setting/roles/delete/') ?>${roleId}`;
    document.getElementById('delete_role_name_span').innerText = roleName;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteRoleModal'));
    deleteModal.show();
}

async function openRoleMenuModal(roleId, roleName) {
    const modalEl = document.getElementById('roleMenuModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

    document.getElementById('roleMenuModalLabel').innerText = 'Kelola Akses Menu untuk Role: ' + roleName;
    document.getElementById('role_menu_role_id').value = roleId;

    // Reset checkboxes
    document.querySelectorAll('#roleMenuPermissionsTable input[type="checkbox"]').forEach(cb => {
        cb.checked = false;
        cb.disabled = false; // Also reset disabled state
    });

    try {
        // Fetch current menu permissions for the role
        const response = await fetch(`<?= site_url('setting/roles/getmenus/') ?>${roleId}`);
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({ message: 'Terjadi kesalahan saat mengambil izin menu.' }));
            // Using alert as a fallback since a global showNotification function is not guaranteed.
            alert(errorData.message || 'Gagal memuat izin.');
            return; // Stop execution to prevent modal from showing with incomplete data
        }
        const assignedPermissions = await response.json();

        assignedPermissions.forEach(perm => {
            const menuId = perm.menu_id;
            const row = document.querySelector(`#roleMenuPermissionsTable tr[data-menu-id="${menuId}"]`);
            if (row) {
                const menuUrl = row.dataset.menuUrl;
                const isParentWithoutUrl = !menuUrl;

                const actionCheckboxes = row.querySelectorAll('input[name$="[can_create]"], input[name$="[can_update]"], input[name$="[can_delete]"], input[name$="[can_reset_password]"]');
                actionCheckboxes.forEach(cb => {
                    cb.disabled = isParentWithoutUrl;
                });

                row.querySelector(`input[name="menus[${menuId}][can_view]"]`).checked = (perm.can_view == "1");
                row.querySelector(`input[name="menus[${menuId}][can_create]"]`).checked = (perm.can_create == "1");
                row.querySelector(`input[name="menus[${menuId}][can_update]"]`).checked = (perm.can_update == "1");
                row.querySelector(`input[name="menus[${menuId}][can_delete]"]`).checked = (perm.can_delete == "1");
                if (row.querySelector(`input[name="menus[${menuId}][can_reset_password]"]`)) {
                    row.querySelector(`input[name="menus[${menuId}][can_reset_password]"]`).checked = (perm.can_reset_password == "1");
                }
            } else {
                console.warn(`Menu ID ${menuId} not found in the table for permissions.`);
            }
        });
        modal.show();
    } catch (error) {
        console.error('Error fetching role menu permissions:', error);
        alert('Gagal memuat menu untuk role ini.');
    }
}
</script>

<?= $this->endSection() ?>
