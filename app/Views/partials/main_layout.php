<!DOCTYPE html>
<html>

<head>
    <title><?= $this->renderSection('title') ?: 'Dashboard' ?></title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= base_url('css/dashboard.css') ?>" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="icon" href="<?= base_url('img/gas.png') ?>" type="image/x-icon">
    <!-- Toastify-JS CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
</head>

<body style="min-height:100vh;display:flex;flex-direction:column;">
    <div class="d-flex flex-row" style="min-height:100vh;">
        <?= view('partials/sidebar', ['sidebarMenus' => $sidebarMenus]) ?>
        <div id="mainContent" class="main-content-wrapper d-flex flex-column flex-grow-1" style="min-width:0;">
            <?= view('partials/navbar', ['title' => $title ?? 'Dashboard']) ?>
            <div class="content-section flex-grow-1">
                <?= $this->renderSection('content') ?>
            </div>
        </div>
    </div>
    <?= view('partials/footer') ?>
    <!-- Bootstrap JS and Icons -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= base_url('js/dashboard.js') ?>"></script>

    <!-- Toastify-JS JS -->
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    <script>
        // Global showNotification function using Toastify-JS
        function showNotification(message, type) {
            let backgroundColor;
            if (type === 'success') {
                backgroundColor = "linear-gradient(to right, #00b09b, #96c93d)";
            } else if (type === 'error') {
                backgroundColor = "linear-gradient(to right, #ff5f6d, #ffc371)";
            } else { // info or default
                backgroundColor = "linear-gradient(to right, #007bff, #6c757d)";
            }

            Toastify({
                text: message,
                duration: 3000, // Durasi notifikasi dalam ms
                close: true, // Izinkan user menutup notifikasi
                gravity: "top", // `top` or `bottom`
                position: "right", // `left`, `center` or `right`
                style: { background: backgroundColor },
            }).showToast();
        }

        // Tampilkan notifikasi flashdata saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (session()->getFlashdata('success')): ?>
                showNotification('<?= esc(session()->getFlashdata('success'), 'js') ?>', 'success');
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                showNotification('<?= esc(session()->getFlashdata('error'), 'js') ?>', 'error');
            <?php endif; ?>
            <?php if (session()->getFlashdata('access_denied_error')): ?>
                showNotification('<?= esc(session()->getFlashdata('access_denied_error'), 'js') ?>', 'error');
            <?php endif; ?>
            <?php if (session()->getFlashdata('errors')): ?>
                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                    showNotification('<?= esc($error, 'js') ?>', 'error');
                <?php endforeach; ?>
            <?php endif; ?>
        });
    </script>
</body>

</html>