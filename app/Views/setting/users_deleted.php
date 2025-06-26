<?= $this->extend('partials/main_layout') ?>

<?= $this->section('content') ?>
<div class="container mt-5">
    <h3>User yang Dihapus</h3>
    <p>Hanya Super Admin yang dapat melihat dan memulihkan user dari halaman ini.</p>

    <a href="<?= site_url('setting/users') ?>" class="btn btn-secondary mb-3"><i class="bi bi-arrow-left"></i> Kembali ke Manajemen User</a>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Username</th>
                <th>Nama Lengkap</th>
                <th>Email</th>
                <th>Tanggal Dihapus</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php if (!empty($users)): ?>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= esc($user['username']) ?></td>
                    <td><?= esc($user['name']) ?></td>
                    <td><?= esc($user['email']) ?></td>
                    <td><?= esc($user['deleted_at']) ?></td>
                    <td>
                        <button class="btn btn-success btn-sm" onclick="openRestoreModal(<?= $user['id'] ?>, '<?= esc($user['username']) ?>')">
                            <i class="bi bi-arrow-counterclockwise"></i> Restore
                        </button>
                    </td>
                </tr>
            <?php endforeach ?>
        <?php else: ?>
            <tr>
                <td colspan="5" class="text-center">Tidak ada data user yang dihapus.</td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal Restore User -->
<div class="modal fade" id="restoreUserModal" tabindex="-1" aria-labelledby="restoreUserModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="restoreUserForm" class="modal-content" method="post">
      <?= csrf_field() ?>
      <div class="modal-header">
        <h5 class="modal-title" id="restoreUserModalLabel">Konfirmasi Pemulihan User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Anda yakin ingin memulihkan user <strong id="restore_username_span"></strong>?</p>
        <p class="text-muted small">User akan dapat login kembali ke sistem.</p>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-success">Ya, Pulihkan User</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
      </div>
    </form>
  </div>
</div>

<script>
function openRestoreModal(userId, username) {
    const form = document.getElementById('restoreUserForm');
    form.action = `<?= site_url('setting/users/restore/') ?>${userId}`;
    document.getElementById('restore_username_span').innerText = username;
    const restoreModal = new bootstrap.Modal(document.getElementById('restoreUserModal'));
    restoreModal.show();
}
</script>
<?= $this->endSection() ?>