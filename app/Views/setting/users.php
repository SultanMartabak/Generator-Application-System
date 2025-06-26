<?= $this->extend('partials/main_layout') ?>

<?= $this->section('content') ?>
<div class="container mt-5 p-3">

    <h2 class="mb-4">Manajemen User</h2>

    <?php use App\Filters\Rbac; // Import kelas Rbac ?>
    <?php $isCurrentUserSuperAdmin = (session()->get('role_id') == SUPER_ADMIN_ROLE_ID); ?>

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <?php if (Rbac::userCan('can_create', '/setting/users')): ?>
                <button class="btn btn-success me-2 rounded-pill px-4 py-2 shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#userModal" onclick="openAddUserModal()"><i class="bi bi-plus"></i> Tambah User</button>
            <?php endif; ?>
            <?php if ($isCurrentUserSuperAdmin): ?>
                <a href="<?= site_url('setting/users/trash') ?>" class="btn btn-outline-secondary rounded-pill px-4 py-2 shadow-sm fw-semibold"><i class="bi bi-trash"></i> Lihat User Dihapus</a>
            <?php endif; ?>
        </div>
        <div class="mt-2 mt-md-0 flex-grow-1" style="max-width: 350px; min-width: 220px;">
            <div class="input-group shadow-sm rounded-pill overflow-hidden bg-white border border-2 border-primary-subtle">
                <span class="input-group-text bg-white border-0"><i class="bi bi-search text-primary"></i></span>
                <input class="form-control border-0 bg-white px-3 py-2" type="search" placeholder="Cari user..." id="user-search-input" value="<?= esc($search ?? '') ?>" style="box-shadow:none;">
            </div>
        </div>
    </div>

    <!-- Container untuk daftar user yang akan diupdate oleh AJAX -->
    <div id="user-list-container" class="position-relative">
        <?= view('setting/_user_list_partial', ['users' => $users, 'pager' => $pager, 'isCurrentUserSuperAdmin' => $isCurrentUserSuperAdmin]) ?>
    </div>
</div>

<!-- Modal Tambah/Edit User -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content rounded-4 shadow-lg border-0" method="post" action="<?= site_url('setting/users/save') ?>">
      <?= csrf_field() ?>
      <div class="modal-header bg-primary text-white rounded-top-4 border-0">
        <h5 class="modal-title d-flex align-items-center gap-2" id="userModalLabel"><i class="bi bi-person-plus-fill"></i> Tambah User</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body bg-light-subtle">
        <input type="hidden" name="id" id="user_id_input">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" id="user_username_input" class="form-control rounded-pill" required>
       </div>
        <div class="mb-3">
            <label class="form-label">Nama Lengkap</label>
            <input type="text" name="name" id="user_name_input" class="form-control rounded-pill" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
           <input type="email" name="email" id="user_email_input" class="form-control rounded-pill" required autocomplete="username">
       </div>
       <div id="password-fields-container">
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" id="user_password_input" class="form-control rounded-pill" autocomplete="new-password">
            </div>
            <div class="mb-3">
                <label class="form-label">Konfirmasi Password</label>
                <input type="password" name="confirm_password" id="user_confirm_password_input" class="form-control rounded-pill" autocomplete="new-password">
            </div>
        </div>
        <!-- Kolom khusus Admin -->
        <div id="admin-only-fields">
           <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" role="switch" name="is_active" value="1" id="user_is_active_input">
                <label class="form-check-label" for="user_is_active_input">User Aktif</label>
           </div>
           <hr>
            <div class="mb-3">
                <label class="form-label">Roles</label>
                <div id="roles-checkbox-container">
                    <?php foreach ($roles as $role): ?>
                        <?php
                        // Hide the Super Admin role if the logged-in user is not a Super Admin
                        if ($role['id'] == SUPER_ADMIN_ROLE_ID && !$isCurrentUserSuperAdmin) continue;
                        ?>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="roles[]" value="<?= esc($role['id']) ?>" id="role_<?= esc($role['id']) ?>">
                            <label class="form-check-label" for="role_<?= esc($role['id']) ?>"><?= esc($role['name']) ?></label>
                       </div>
                    <?php endforeach; ?>
                </div>
           </div>
        </div>

      </div>
      <div class="modal-footer bg-light rounded-bottom-4 border-0">
        <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="bi bi-save"></i> Simpan</button>
        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Delete User -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form id="deleteUserForm" class="modal-content rounded-4 shadow-lg border-0" method="post">
      <?= csrf_field() ?>
      <div class="modal-header bg-danger text-white rounded-top-4 border-0">
        <h5 class="modal-title d-flex align-items-center gap-2" id="deleteUserModalLabel"><i class="bi bi-exclamation-triangle-fill"></i> Konfirmasi Hapus User</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body bg-light-subtle">
        <p>Anda yakin ingin menghapus user <strong id="delete_username_span"></strong>?</p>
        <p class="text-muted small">Aksi ini akan menonaktifkan akses user ke sistem, tetapi data historis mereka akan tetap tersimpan. Aksi ini dapat diurungkan oleh administrator.</p>
      </div>
      <div class="modal-footer bg-light rounded-bottom-4 border-0">
        <button type="submit" class="btn btn-danger rounded-pill px-4"><i class="bi bi-trash-fill"></i> Ya, Hapus User</button>
        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Reset Password -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <form class="modal-content rounded-4 shadow-lg border-0" method="post" action="<?= site_url('setting/users/resetpassword') ?>">
      <?= csrf_field() ?>
      <div class="modal-header bg-info text-white rounded-top-4 border-0">
        <h5 class="modal-title d-flex align-items-center gap-2" id="resetPasswordModalLabel"><i class="bi bi-key-fill"></i> Reset Password</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body bg-light-subtle">
        <input type="hidden" name="user_id" id="reset_user_id_input">
        <p>Anda yakin ingin mereset password untuk user <strong id="reset_username_span"></strong>?</p>
        <p>Password akan direset menjadi sama dengan <strong>username</strong>-nya.</p>
      </div>
      <div class="modal-footer bg-light rounded-bottom-4 border-0">
        <button type="submit" class="btn btn-info rounded-pill px-4 text-white"><i class="bi bi-arrow-clockwise"></i> Ya, Reset Password</button>
        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
      </div>
    </form>
  </div>
</div>

<script>
function openAddUserModal() {
    document.getElementById('userModalLabel').innerText = 'Tambah User';
   document.querySelector('#userModal form').reset();
    document.getElementById('user_id_input').value = '';
    document.getElementById('password-fields-container').style.display = 'none'; // Sembunyikan field password untuk user baru
    document.getElementById('user_password_input').removeAttribute('required'); // Hapus atribut required
    document.getElementById('user_confirm_password_input').removeAttribute('required'); // Hapus atribut required
    // Set default status to active for new users
    document.getElementById('user_is_active_input').checked = true;
    // Uncheck all roles
    document.querySelectorAll('#roles-checkbox-container input[type="checkbox"]').forEach(cb => cb.checked = false);
}

function openEditUserModal(user, assignedRoleNames, isSelfEdit = false) {
   const allRoles = <?= json_encode($roles) ?>;
    document.getElementById('userModalLabel').innerText = 'Edit User: ' + user.username;
    document.getElementById('user_id_input').value = user.id;
    document.getElementById('user_username_input').value = user.username;
    document.getElementById('user_name_input').value = user.nama_lengkap; // Pastikan key ini sesuai
    document.getElementById('user_email_input').value = user.email;
    document.getElementById('password-fields-container').style.display = 'none';
    document.getElementById('user_password_input').value = '';
    document.getElementById('user_confirm_password_input').value = '';
    document.getElementById('user_password_input').removeAttribute('required');
    document.getElementById('user_confirm_password_input').removeAttribute('required');
    document.getElementById('user_is_active_input').checked = user.is_active == 1;

    // Tampilkan/sembunyikan kolom khusus admin berdasarkan apakah ini self-edit
    const adminFields = document.getElementById('admin-only-fields');
    if (isSelfEdit) {
        adminFields.style.display = 'none';
   } else {
        adminFields.style.display = 'block';
    }
    // Uncheck all roles first
    document.querySelectorAll('#roles-checkbox-container input[type="checkbox"]').forEach(cb => cb.checked = false);
    
    // Check the roles that are assigned to the user
    allRoles.forEach(role => {
       if (assignedRoleNames.includes(role.name)) {
            document.getElementById(`role_${role.id}`).checked = true;
        }
    });
}

function openResetPasswordModal(userId, username) {
    document.getElementById('reset_user_id_input').value = userId;
    document.getElementById('reset_username_span').innerText = username;
}

function openDeleteModal(userId, username) {
    const form = document.getElementById('deleteUserForm');
    form.action = `<?= site_url('setting/users/delete/') ?>${userId}`;
    document.getElementById('delete_username_span').innerText = username;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
    deleteModal.show();
}

/**
 * Initializes UI components that might be added dynamically,
 * like dropdowns in the user list.
 */
function initializeUIComponents() {
    // Disable dropdown toggle on hover, activate only on click.
    // This is crucial for dropdowns inside tables that are dynamically reloaded.
    document.querySelectorAll('.dropdown-toggle').forEach(function(btn) {
        // Remove any existing listeners to prevent duplicates if this function is called multiple times.
        btn.removeEventListener('mouseover', stopPropagation);
        btn.removeEventListener('mouseenter', stopPropagation);
        // Add the new listeners.
        btn.addEventListener('mouseover', stopPropagation);
        btn.addEventListener('mouseenter', stopPropagation);
    });
}

function stopPropagation(e) {
    e.stopPropagation();
    e.preventDefault();
}

document.addEventListener('DOMContentLoaded', function () {
    // // Tampilkan notifikasi ToastifyJS dari flash data
    // // Pastikan Toastify.js dan CSS-nya sudah dimuat di layout utama Anda.
    // if (typeof Toastify !== 'undefined') {
    //     <?php if (session()->getFlashdata('success')): ?>
    //         Toastify({
    //             text: '<?= esc(session()->getFlashdata('success'), 'js') ?>',
    //             duration: 3000,
    //             close: true,
    //             gravity: "top", // `top` or `bottom`
    //             position: "right", // `left`, `center` or `right`
    //             stopOnFocus: true, // Prevents dismissing of toast on hover
    //             style: {
    //                 background: "linear-gradient(to right, #00b09b, #96c93d)",
    //             },
    //         }).showToast();
    //     <?php endif; ?>

    //     <?php if (session()->getFlashdata('error')): ?>
    //         Toastify({
    //             text: '<?= esc(session()->getFlashdata('error'), 'js') ?>',
    //             duration: 4000,
    //             close: true,
    //             gravity: "top",
    //             position: "right",
    //             stopOnFocus: true,
    //             style: {
    //                 background: "linear-gradient(to right, #ff5f6d, #ffc371)",
    //             },
    //         }).showToast();
    //     <?php endif; ?>

    //     <?php if (session()->getFlashdata('errors')): ?>
    //         <?php foreach (session()->getFlashdata('errors') as $error): ?>
    //             Toastify({
    //                 text: '<?= esc($error, 'js') ?>',
    //                 duration: 4000,
    //                 close: true,
    //                 gravity: "top",
    //                 position: "right",
    //                 stopOnFocus: true,
    //                 style: {
    //                     background: "linear-gradient(to right, #ff5f6d, #ffc371)",
    //                 },
    //             }).showToast();
    //         <?php endforeach; ?>
    //     <?php endif; ?>
    // }

    const searchInput = document.getElementById('user-search-input');
    const userListContainer = document.getElementById('user-list-container');
    let debounceTimer;

    // Fungsi untuk mengambil data user
    async function fetchUsers(page = 1, searchTerm = '') {
        userListContainer.innerHTML = '<div class="text-center p-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
        try {
            const url = new URL('<?= site_url('setting/users') ?>');
            url.searchParams.set('page', page);
            if (searchTerm) {
                url.searchParams.set('search', searchTerm);
            }
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            // Asumsikan response.html sudah berisi seluruh partial (tabel + pagination modern)
            const data = await response.json();
            userListContainer.innerHTML = data.html;
            
            // Re-initialize UI components for the new content
            initializeUIComponents();
        } catch (error) {
            userListContainer.innerHTML = '<div class="alert alert-danger">Gagal memuat data. Silakan coba lagi.</div>';
        }
    }

    // Event listener untuk input pencarian dengan debounce
    searchInput.addEventListener('keyup', (e) => {
        clearTimeout(debounceTimer);
        const searchTerm = e.target.value;
        debounceTimer = setTimeout(() => {
            fetchUsers(1, searchTerm); // Selalu kembali ke halaman 1 saat pencarian baru
        }, 500);
    });

    // Event listener untuk klik pagination (delegasi event)
    document.getElementById('user-list-container').addEventListener('click', (e) => {
        const target = e.target.closest('a.page-link');
        if (target) {
            e.preventDefault();
            const url = new URL(target.href);
            const page = url.searchParams.get('page');
            const searchTerm = searchInput.value;
            if (page) {
                fetchUsers(page, searchTerm);
            }
        }
    });

    // Initialize components for the first time on page load
    initializeUIComponents();
});


</script>
<?= $this->endSection() ?>