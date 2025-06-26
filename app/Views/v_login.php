<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>General Affairs System | Login Page</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="<?= base_url('css/login.css') ?>">
    <link rel="icon" href="<?= base_url('img/gas.png') ?>" type="image/x-icon">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo-circle">
                <img src="<?= base_url('img/gas.png') ?>" alt="Logo GAS" class="logo-img">
            </div>
            <div class="login-title">LOG IN</div>
            <form method="post" action="<?= site_url('login/auth') ?>">
                <?= csrf_field() ?>
                <div class="form-group">
                    <span class="input-icon"><i class="fa fa-user"></i></span>
                    <input type="text" id="username" name="username" placeholder="Username" required>
                </div>
                <div class="form-group">
                    <span class="input-icon"><i class="fa fa-lock"></i></span>
                    <input type="password" id="password" name="password" placeholder="Password" required>
                </div>
                <?php if (session()->getFlashdata('error')): ?>
                    <p style="color:red;"><?= session()->getFlashdata('error') ?></p>
                <?php endif; ?>
                <div class="remember-row">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Remember me</label>
                </div>
                <button type="submit" class="login-btn">Login</button>
            </form>
            <a href="#" class="forgot-link">Forgot Password?</a>
        </div>
    </div>
    <!-- Bootstrap 5 JS (optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>