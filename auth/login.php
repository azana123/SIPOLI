<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <!-- style,css -->
    <link rel="stylesheet" href="./../assets/css/style.css">
    <!-- Google Icon CDN -->
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
            <span class="bg-primary-100 text-primary text-sm font-medium px-2 py-1 rounded">Admin</span>
            <h2 class="text-xl font-bold mt-2">Masuk ke dashboard admin</h2>
            <p>Gunakan Nomor Induk dan password yang terdaftar.</p>

            <form action="http://localhost/sipoli/auth/login.php" method="post" class="flex flex-col mt-5">
                <div class="mb-5">
                    <label for="email" class="block mb-2.5 text-sm font-medium text-heading">Nomor Induk</label>
                    <input type="email" id="email" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body" placeholder="Masukkan Nomor Induk" required />
                </div>
                <div class="mb-5">
                    <label for="password" class="block mb-2.5 text-sm font-medium text-heading">Password</label>
                    <input type="password" id="password" class="bg-neutral-secondary-medium border border-default-medium text-heading text-sm rounded-base focus:ring-brand focus:border-brand block w-full px-3 py-2.5 shadow-xs placeholder:text-body" placeholder="••••••••" required />
                </div>
                <button class="bg-primary hover:bg-primary-600 py-3 rounded-full w-full text-page" type="submit">Login</button>
        </section>
    </div>
    <script src="./../assets/js/app.js"></script>
</body>

</html>