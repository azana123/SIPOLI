<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../middleware/csrf.php';

// Sudah login -> tidak perlu mendaftar lagi.
if (isset($_SESSION['user_id'], $_SESSION['role'])) {
    require_once __DIR__ . '/../middleware/check-role.php';
    redirect_by_role((string) $_SESSION['role']);
}

$error = flash_pull('error');
$old   = $_SESSION['old'] ?? [];
unset($_SESSION['old']);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIPOLI Daftar</title>
    <!-- style.css -->
    <link rel="stylesheet" href="./../assets/css/style.css">
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.1.0/css/all.css" integrity="sha512-ajhUYg8JAATDFejqbeN7KbF2zyPbbqz04dgOLyGcYEk/MJD3V+HJhJLKvJ2VVlqrr4PwHeGTTWxbI+8teA7snw==" crossorigin="anonymous" referrerpolicy="no-referrer">
</head>

<body>
    <div class="w-full min-h-screen bg-page overflow-hidden flex flex-col md:flex-row md:flex-row-reverse">
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
        <section class="login-content md:w-1/2 mt-8 px-6 md:px-8 md:pb-5">
            <h2 class="text-xl font-bold">Daftar Akun</h2>
            <p>Khusus civitas akademika. Data Anda dicocokkan dengan data kampus.</p>

            <form action="process-register.php" method="post" class="flex flex-col mt-5">
                <?= csrf_field() ?>

                <?php if ($error): ?>
                    <div class="mb-4 p-3 bg-red-100 text-red-700 rounded-base text-sm">
                        <?= nl2br(e($error)) ?>
                    </div>
                <?php endif; ?>

                <div class="mb-5">
                    <label for="nim_nip" class="block mb-2.5 text-sm font-medium text-heading">NIM/NIP</label>
                    <input type="text" name="nim_nip" id="nim_nip" inputmode="numeric" pattern="[0-9]{8,20}" maxlength="20" value="<?= e($old['nim_nip'] ?? '') ?>" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body" placeholder="Masukkan NIM atau NIP" required />
                </div>

                <div class="mb-5">
                    <label for="tanggal_lahir" class="block mb-2.5 text-sm font-medium text-heading">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" id="tanggal_lahir" max="<?= e(date('Y-m-d')) ?>" value="<?= e($old['tanggal_lahir'] ?? '') ?>" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body" required />
                    <p class="mt-1 text-xs text-gray-500">Dipakai untuk memverifikasi bahwa NIM/NIP ini milik Anda.</p>
                </div>

                <div class="mb-5">
                    <label for="username" class="block mb-2.5 text-sm font-medium text-heading">Username</label>
                    <input type="text" name="username" id="username" minlength="4" maxlength="30" autocomplete="username" value="<?= e($old['username'] ?? '') ?>" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body" placeholder="Masukkan username" required />
                </div>

                <div class="mb-5">
                    <label for="password" class="block mb-2.5 text-sm font-medium text-heading">Password</label>
                    <input type="password" name="password" id="password" minlength="8" maxlength="72" autocomplete="new-password" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body" placeholder="Minimal 8 karakter" required />
                </div>

                <div class="mb-5">
                    <label for="confirm_password" class="block mb-2.5 text-sm font-medium text-heading">Konfirmasi Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" minlength="8" maxlength="72" autocomplete="new-password" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body" placeholder="••••••••" required />
                </div>

                <button class="bg-primary hover:bg-primary-600 py-2.5 text-sm rounded-full w-full text-page mt-2" type="submit">Daftar</button>

            </form>
            <div class="mt-2">
                <p class="text-center">Sudah memiliki akun? <a href="./login.php" class="text-primary hover:text-primary-700 underline">Login</a> disini</p>
            </div>
        </section>
    </div>
    <script src="./../assets/js/app.js"></script>
</body>

</html>