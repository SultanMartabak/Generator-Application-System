<?= $this->extend('partials/main_layout') ?>

<?= $this->section('content') ?>
<div class="container mt-5">
    <h3>Role yang Dihapus</h3>
    <p>Hanya Super Admin yang dapat melihat dan memulihkan role dari halaman ini.</p>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <a href="<?= site_url('setting/roles') ?>" class="btn btn-secondary mb-3"><i class="bi bi-arrow-left"></i> Kembali ke Manajemen Role</a>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Nama Role</th>
                <th>Deskripsi</th>
                <th>Tanggal Dihapus</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php if (!empty($roles)): ?>
            <?php foreach ($roles as $role): ?>
                <tr>
                    <td><?= esc($role['name']) ?></td>
                    <td><?= esc($role['description']) ?></td>
                    <td><?= esc($role['deleted_at']) ?></td>
                    <td>
                        <button class="btn btn-success btn-sm" onclick="openRestoreModal(<?= $role['id'] ?>, '<?= esc($role['name'], 'js') ?>')">
                            <i class="bi bi-arrow-counterclockwise"></i> Restore
                        </button>
                    </td>
                </tr>
            <?php endforeach ?>
        <?php else: ?>
            <tr>
                <td colspan="4" class="text-center">Tidak ada data role yang dihapus.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal Restore Role -->
<div class="modal fade" id="restoreRoleModal" tabindex="-1" aria-labelledby="restoreRoleModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="restoreRoleForm" class="modal-content" method="post">
      <?= csrf_field() ?>
      <div class="modal-header">
        <h5 class="modal-title" id="restoreRoleModalLabel">Konfirmasi Pemulihan Role</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Anda yakin ingin memulihkan role <strong id="restore_role_name_span"></strong>?</p>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Ya, Pulihkan Role</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
      </div>
    </form>
  </div>
</div>

<script>
function openRestoreModal(roleId, roleName) {
    const form = document.getElementById('restoreRoleForm');
    form.action = `<?= site_url('setting/roles/restore/') ?>${roleId}`;
    document.getElementById('restore_role_name_span').innerText = roleName;
    const restoreModal = new bootstrap.Modal(document.getElementById('restoreRoleModal'));
    restoreModal.show();
}
</script>
<?= $this->endSection() ?>