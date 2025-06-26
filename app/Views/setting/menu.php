<?= $this->extend('partials/main_layout') ?>

<?= $this->section('content') ?>
<?php
use App\Filters\Rbac;
$canCreate = Rbac::userCan('can_create', 'setting/menu');
$canUpdate = Rbac::userCan('can_update', 'setting/menu');
$canDelete = Rbac::userCan('can_delete', 'setting/menu');
// // Tambahkan ini untuk debugging
// echo '<pre>';
// echo 'canCreate: ' . ($canCreate ? 'true' : 'false') . '<br>';
// echo 'canUpdate: ' . ($canUpdate ? 'true' : 'false') . '<br>';
// echo 'canDelete: ' . ($canDelete ? 'true' : 'false') . '<br>';
// echo '</pre>';
?>
<style>
    /* Simple styling for the draggable menu */
    .menu-list { list-style-type: none; padding-left: 0; }
    .menu-list ul { list-style-type: none; padding-left: 25px; margin-top: 10px; }
    .menu-item {
        display: flex;
        align-items: center;
        padding: 10px;
        border: 1px solid #ddd;
        background-color: #f9f9f9;
        margin-bottom: 5px;
        border-radius: 4px;
    }
    .menu-item .bi-grip-vertical {
        cursor: grab;
    }
    .menu-item:active { cursor: grabbing; }
    .menu-item .menu-icon { margin-right: 10px; }
    .menu-item .menu-actions { margin-left: auto; }
    .sortable-ghost { background-color: #cce5ff; }
</style>

<div class="container mt-3 p-5">
    <h3>Manajemen Menu</h3>
    <p>Seret dan lepas item menu untuk mengatur urutan dan hirarkinya.</p>

    <div id="notification-area"></div>

    <?php if ($canCreate): ?>
    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#menuModal" onclick="openAddMenuModal()">Tambah Menu</button>
    <?php endif; ?>
    <?php if ($canUpdate): ?>
    <div class="d-inline-block">
        <button id="saveOrderBtn" class="btn btn-success mb-3 me-2" style="display: none;">Simpan Urutan</button>
        <button id="cancelOrderBtn" class="btn btn-secondary mb-3" style="display: none;">Batal</button>
    </div>
    <?php endif; ?>
    <?php if (session()->get('role_id') == SUPER_ADMIN_ROLE_ID): ?>
        <a href="<?= site_url('setting/menu/trash') ?>" class="btn btn-outline-secondary mb-3"><i class="bi bi-trash"></i> Lihat Menu Dihapus</a>
    <?php endif; ?>

    <div id="menu-container">
        <ul id="menu-list-root" class="menu-list">
            <?php
            // Helper function to render the menu tree recursively
            function renderMenuNode(array $menuNodes, bool $canUpdate, bool $canDelete) {
                foreach ($menuNodes as $node) {
                    echo '<li data-id="' . $node['id'] . '">';
                    echo '<div class="menu-item">';
                    if ($canUpdate) {
                        echo '<i class="bi bi-grip-vertical"></i> &nbsp;';
                    }
                    echo '<i class="' . esc($node['icon']) . ' menu-icon"></i>';
                    echo '<span>' . esc($node['name']) . '</span>';
                    echo '<div class="menu-actions">';
                    if ($canUpdate) {
                        echo '<button class="btn btn-warning btn-sm me-2" onclick=\'openEditMenuModal(' . json_encode($node) . ')\'>Edit</button>';
                    }
                    if ($canDelete) {
                        echo '<button class="btn btn-danger btn-sm" onclick="openDeleteMenuModal(' . $node['id'] . ', \'' . esc($node['name'], 'js') . '\')">Hapus</button>';
                    }
                    echo '</div>';
                    echo '</div>';

                    if (!empty($node['children'])) {
                        echo '<ul class="menu-list">'; // Render nested UL
                        renderMenuNode($node['children'], $canUpdate, $canDelete);
                        echo '</ul>';
                    }
                    // Always render an empty nested UL if no children, to act as a drop target
                    else { echo '<ul class="menu-list"></ul>'; }
                    echo '</li>';
                }
            }
            renderMenuNode($menus, $canUpdate, $canDelete);
            ?>
        </ul>
    </div>
</div>

<!-- Modal Tambah/Edit Menu (Anda mungkin sudah punya ini, sesuaikan jika perlu) -->
<div class="modal fade" id="menuModal" tabindex="-1" aria-labelledby="menuModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="menuForm" class="modal-content" method="post" action="<?= site_url('setting/menu/save') ?>">
            <?= csrf_field() ?>
            <div class="modal-header">
                <h5 class="modal-title" id="menuModalLabel">Tambah Menu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="menu_id_input">
                <div class="mb-3">
                    <label for="menu_name_input" class="form-label">Nama Menu</label>
                    <input type="text" name="name" id="menu_name_input" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="menu_url_input" class="form-label">URL</label>
                    <input type="text" name="url" id="menu_url_input" class="form-control" placeholder="/contoh/halaman">
                </div>
                <div class="mb-3">
                    <label for="menu_icon_input" class="form-label">Ikon (contoh: bi bi-speedometer2)</label>
                    <input type="text" name="icon" id="menu_icon_input" class="form-control">
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch" name="is_active" value="1" id="menu_is_active_input">
                    <label class="form-check-label" for="menu_is_active_input">Aktif (bisa diakses oleh role)</label>
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch" name="is_visible" value="1" id="menu_is_visible_input">
                    <label class="form-check-label" for="menu_is_visible_input">Terlihat (tampil di sidebar)</label>
                </div>
                <div class="form-check form-switch mb-3" id="generate_scaffold_container">
                    <input class="form-check-input" type="checkbox" role="switch" name="generate_scaffold" value="1" id="menu_generate_scaffold_input">
                    <label class="form-check-label" for="menu_generate_scaffold_input">Generate CRUD (Controller, Model, Views)</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Delete Menu -->
<div class="modal fade" id="deleteMenuModal" tabindex="-1" aria-labelledby="deleteMenuModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="deleteMenuForm" class="modal-content" method="post">
      <?= csrf_field() ?>
      <div class="modal-header">
        <h5 class="modal-title" id="deleteMenuModalLabel">Konfirmasi Hapus Menu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Anda yakin ingin menghapus menu <strong id="delete_menu_name_span"></strong>?</p>
        <p class="text-danger small">Perhatian: Menghapus menu induk juga akan menghapus semua submenu di bawahnya.</p>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-danger">Ya, Hapus Menu</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Route Suggestion -->
<div class="modal fade" id="routeSuggestionModal" tabindex="-1" aria-labelledby="routeSuggestionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="routeSuggestionModalLabel"><i class="bi bi-sign-turn-right"></i> Saran Penambahan Rute</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>File CRUD berhasil dibuat. Tambahkan kode berikut ke file <strong>app/Config/Routes.php</strong> Anda untuk mengaktifkannya.</p>
        <div class="bg-light p-3 border rounded position-relative">
            <pre><code id="route-code-block" class="language-php" style="white-space: pre-wrap; word-break: break-all;"></code></pre>
            <button id="copy-route-btn" class="btn btn-sm btn-outline-secondary position-absolute" style="top: 10px; right: 10px;" title="Copy to clipboard">
                <i class="bi bi-clipboard"></i> Copy
            </button>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- CDN untuk SortableJS -->
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const canUpdate = <?= $canUpdate ? 'true' : 'false' ?>;

    if (canUpdate) {
        const menuContainer = document.getElementById('menu-list-root');
        const saveOrderBtn = document.getElementById('saveOrderBtn');
        const cancelOrderBtn = document.getElementById('cancelOrderBtn');
        const notificationArea = document.getElementById('notification-area');

        // Initialize Sortable on all lists
        const allLists = document.querySelectorAll('.menu-list');
        allLists.forEach(list => {
            new Sortable(list, {
                group: 'nested',
                animation: 150,
                handle: '.bi-grip-vertical', // This is the crucial line
                fallbackOnBody: false, // Mengubah ini menjadi false untuk penargetan drop yang lebih presisi
                swapThreshold: 0.5, // Mengurangi nilai ini agar lebih mudah untuk nesting
                onEnd: function() {
                    // Show the save button when user makes a change
                    saveOrderBtn.style.display = 'inline-block';
                    cancelOrderBtn.style.display = 'inline-block';
                }
            });
        });

        // Save Order Button Logic
        saveOrderBtn.addEventListener('click', function() {
            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...';

            const structure = serialize(menuContainer);

            fetch('<?= site_url('setting/menu/updateorder') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
                },
                body: JSON.stringify(structure)
            })
            .then(response => {
                // Cek jika response tidak OK (misal: status 403, 404, 500)
                if (!response.ok) {
                    // Coba baca response sebagai JSON untuk mendapatkan pesan error dari server
                    return response.json().then(err => {
                        // Lemparkan error dengan pesan dari server jika ada
                        throw new Error(err.message || `Terjadi kesalahan server (Status: ${response.status})`);
                    }).catch(() => {
                        // Jika response bukan JSON (misal: halaman HTML error), lemparkan error umum
                        throw new Error(`Server merespon dengan status ${response.status}. Pastikan endpoint mengembalikan JSON.`);
                    });
                }
                return response.json(); // Lanjutkan jika response OK
            })
            .then(data => {
                showNotification(data.message, data.status);
                // Hide both buttons after saving or on error (user might need to re-drag)
                cancelOrderBtn.style.display = 'none';
                saveOrderBtn.style.display = 'none';
                // Reload the page to reflect changes (or reset if save failed)
                setTimeout(function() {
                    location.reload();
                }, 1500); // Delay ditambah agar user sempat membaca notifikasi
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification(error.message, 'error');
            })
            .finally(() => {
                this.disabled = false;
                this.innerHTML = 'Simpan Urutan';
            });
        });

        // Function to serialize the menu structure into a flat array
        function serialize(list, parentId = null) {
            let serialized = [];
            const items = list.children;
            for (let i = 0; i < items.length; i++) {
                const item = items[i];
                const id = item.getAttribute('data-id');
                serialized.push({
                    id: id,
                    parent_id: parentId,
                    sort_order: i
                });

                const nestedList = item.querySelector('ul');
                if (nestedList) {
                    serialized = serialized.concat(serialize(nestedList, id));
                }
            }
            return serialized;
        }

        // Cancel Button Logic
        cancelOrderBtn.addEventListener('click', function() {
            location.reload();
        });
    }

     // Logic for Route Suggestion Modal
    <?php if (session()->getFlashdata('route_suggestion')): ?>
        const routeCode = `<?= esc(session()->getFlashdata('route_suggestion'), 'js') ?>`;
        const routeCodeBlock = document.getElementById('route-code-block');
        const copyBtn = document.getElementById('copy-route-btn');
        
        routeCodeBlock.textContent = routeCode;

        const routeModal = new bootstrap.Modal(document.getElementById('routeSuggestionModal'));
        routeModal.show();

        copyBtn.addEventListener('click', function() {
            navigator.clipboard.writeText(routeCode).then(() => {
                const originalText = this.innerHTML;
                this.innerHTML = '<i class="bi bi-check-lg"></i> Copied!';
                setTimeout(() => {
                    this.innerHTML = originalText;
                }, 2000);
            }).catch(err => {
                console.error('Failed to copy text: ', err);
                alert('Gagal menyalin teks.');
            });
        });
    <?php endif; ?>
});

// Anda perlu mengimplementasikan fungsi-fungsi modal ini sesuai dengan yang sudah ada
function openAddMenuModal() {
    const form = document.getElementById('menuForm');
    form.reset(); // Reset all form fields
    document.getElementById('menuModalLabel').innerText = 'Tambah Menu';
    document.getElementById('menu_id_input').value = '';
    document.getElementById('menu_is_active_input').checked = true;
    document.getElementById('menu_is_visible_input').checked = true;

    // Show generate scaffold option only for new menus
    document.getElementById('generate_scaffold_container').style.display = 'block';
    document.getElementById('menu_generate_scaffold_input').checked = false; // Uncheck by default


    const menuModal = new bootstrap.Modal(document.getElementById('menuModal'));
    menuModal.show();
}

function openEditMenuModal(menuData) {
    document.getElementById('menuModalLabel').innerText = 'Edit Menu: ' + menuData.name;
    document.getElementById('menu_id_input').value = menuData.id;
    document.getElementById('menu_name_input').value = menuData.name;
    document.getElementById('menu_url_input').value = menuData.url;
    document.getElementById('menu_icon_input').value = menuData.icon;
    document.getElementById('menu_is_active_input').checked = menuData.is_active == 1;
    document.getElementById('menu_is_visible_input').checked = menuData.is_visible == 1;

     // Hide generate scaffold option for existing menus
    document.getElementById('generate_scaffold_container').style.display = 'none';

    const menuModal = new bootstrap.Modal(document.getElementById('menuModal'));
    menuModal.show();
}

function openDeleteMenuModal(menuId, menuName) {
    const form = document.getElementById('deleteMenuForm');
    form.action = `<?= site_url('setting/menu/delete/') ?>${menuId}`;
    document.getElementById('delete_menu_name_span').innerText = menuName;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteMenuModal'));
    deleteModal.show();
}
</script>
<?= $this->endSection() ?>
