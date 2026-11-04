<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../middleware/csrf.php';

if (isset($_SESSION['user_id'], $_SESSION['role'])) {
    require_once __DIR__ . '/../middleware/check-role.php';
    redirect_by_role((string) $_SESSION['role']);
}

$error   = flash_pull('error');
$success = flash_pull('success');
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIPOLI Login</title>
    <!-- style.css -->
    <link rel="stylesheet" href="./../assets/css/style.css">
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.1.0/css/all.css" integrity="sha512-ajhUYg8JAATDFejqbeN7KbF2zyPbbqz04dgOLyGcYEk/MJD3V+HJhJLKvJ2VVlqrr4PwHeGTTWxbI+8teA7snw==" crossorigin="anonymous" referrerpolicy="no-referrer">
</head>

<body>
    <div class="w-full min-h-screen bg-page overflow-hidden flex flex-col md:flex-row">
        <section class="login-sidebar bg-primary-500 md:w-1/2 p-4 flex flex-col">
            <div class="brand-name">
                <h1 class="text-2xl font-bold text-white">Sipoli</h1>
            </div>
            <div class="flex flex-col flex-1 items-center justify-center">
                <div class="content">
                    <div class="icon-login w-20 h-20 rounded-full bg-primary-400 mx-auto flex justify-center items-center mb-3">
                        <i class="far fa-calendar-alt text-white text-3xl"></i>
                    </div>
                    <div class="desc">
                        <p class="text-white w-2/3 text-center mx-auto text-lg">Kelola jadwal, reservasi, dan layanan poliklinik</p>
                    </div>
                </div>
            </div>
        </section>
        <section class="login-content  md:w-1/2 mt-8 px-6 md:px-8">
            <h2 class="text-xl font-bold">Masuk ke dashboard</h2>
            <p>Gunakan username dan password yang terdaftar.</p>

            <form action="process-login.php" method="post" class="flex flex-col mt-5">
                <?= csrf_field() ?>

                <?php if ($success): ?>
                    <div class="mb-4 p-3 bg-green-100 text-green-700 rounded-base text-sm">
                        <?= e($success) ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-base text-sm">
                        <?= nl2br(e($error)) ?>
                    </div>
                <?php endif; ?>

                <div class="mb-5">
                    <label for="username" class="block mb-2.5 text-sm font-medium text-heading">Username</label>
                    <input type="text" name="username" id="username" maxlength="50" autocomplete="username" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body" placeholder="Masukkan username" required />
                </div>
                <div class="mb-5">
                    <label for="password" class="block mb-2.5 text-sm font-medium text-heading">Password</label>
                    <input type="password" name="password" id="password" autocomplete="current-password" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body" placeholder="••••••••" required />
                </div>
                <button class="bg-primary hover:bg-primary-600 py-3 rounded-full w-full text-page" type="submit">Login</button>
            </form>
            <div class="mt-2">
                <p class="text-center">Belum memiliki akun? <a href="./register.php" class="text-primary hover:text-primary-700 underline">Daftar</a> sekarang!</p>
            </div>
        </section>
    </div>
    <script src="./../assets/js/app.js"></script>
</body>

</html>