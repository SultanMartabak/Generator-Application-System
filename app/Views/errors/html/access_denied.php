<?= $this->extend('partials/main_layout') ?>

<?= $this->section('title') ?>
    Akses Ditolak
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <h1 class="display-4 text-danger">403</h1>
            <h2 class="mb-3">Akses Ditolak</h2>
            <p class="lead">Maaf, Anda tidak memiliki izin untuk mengakses halaman ini.</p>
            <p>Silakan kembali ke halaman sebelumnya atau hubungi administrator jika Anda merasa ini adalah kesalahan.</p>
            <a href="<?= site_url('dashboard') ?>" class="btn btn-primary mt-3">Kembali ke Dashboard</a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>