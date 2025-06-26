<?= $this->extend('partials/main_layout') ?>

<?= $this->section('content') ?>
<div class="container mt-5">
    <h3>Menu yang Dihapus</h3>
    <p>Hanya Super Admin yang dapat melihat dan memulihkan menu dari halaman ini.</p>

    <a href="<?= site_url('setting/menu') ?>" class="btn btn-secondary mb-3"><i class="bi bi-arrow-left"></i> Kembali ke Manajemen Menu</a>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Nama Menu</th>
                <th>URL</th>
                <th>Tanggal Dihapus</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php if (!empty($menus)): ?>
            <?php foreach ($menus as $menu): ?>
                <tr>
                    <td><?= esc($menu['name']) ?></td>
                    <td><?= esc($menu['url']) ?></td>
                    <td><?= esc($menu['deleted_at']) ?></td>
                    <td>
                      <button type="button" class="btn btn-success btn-sm" onclick="openRestoreModal(<?= $menu['id'] ?>, '<?= esc($menu['name'], 'js') ?>')">Pulihkan</button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="openPurgeModal(<?= $menu['id'] ?>, '<?= esc($menu['name'], 'js') ?>')">Hapus Permanen</button>
                    </td>
                </tr>
            <?php endforeach ?>
        <?php else: ?>
            <tr>
                <td colspan="4" class="text-center">Tidak ada data menu yang dihapus.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal Restore Menu -->
<div class="modal fade" id="restoreMenuModal" tabindex="-1" aria-labelledby="restoreMenuModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="restoreMenuForm" class="modal-content" method="post">
      <?= csrf_field() ?>
      <div class="modal-header">
        <h5 class="modal-title" id="restoreMenuModalLabel">Konfirmasi Pemulihan Menu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Anda yakin ingin memulihkan menu <strong id="restore_menu_name_span"></strong>?</p>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Ya, Pulihkan Menu</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Purge Menu -->
<div class="modal fade" id="purgeMenuModal" tabindex="-1" aria-labelledby="purgeMenuModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="purgeMenuForm" class="modal-content" method="post">
      <?= csrf_field() ?>
      <div class="modal-header">
        <h5 class="modal-title" id="purgeMenuModalLabel">Konfirmasi Hapus Permanen Menu</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Anda yakin ingin menghapus menu <strong id="purge_menu_name_span"></strong> secara permanen?</p>
        <p class="text-danger small">Aksi ini tidak dapat dibatalkan dan semua file terkait (Controller, Model, Views) akan dihapus dari server.</p>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-danger">Ya, Hapus Permanen</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
      </div>
    </form>
  </div>
</div>

<script>
// Fungsi untuk membuka modal konfirmasi Pulihkan
function openRestoreModal(menuId, menuName) {
    const form = document.getElementById('restoreMenuForm');
    form.action = `<?= site_url('setting/menu/restore/') ?>${menuId}`;
    document.getElementById('restore_menu_name_span').innerText = menuName;
    const restoreModal = new bootstrap.Modal(document.getElementById('restoreMenuModal'));
    restoreModal.show();
}

// Fungsi untuk membuka modal konfirmasi Hapus Permanen
function openPurgeModal(menuId, menuName) {
    const form = document.getElementById('purgeMenuForm');
    form.action = `<?= site_url('setting/menu/purge/') ?>${menuId}`;
    document.getElementById('purge_menu_name_span').innerText = menuName;
    const purgeModal = new bootstrap.Modal(document.getElementById('purgeMenuModal'));
    purgeModal.show();
}
</script>
<?= $this->endSection() ?>