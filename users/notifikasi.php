<?php
$page_title = "Notifikasi";
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> | SIPOLI</title>
    <!-- Pastikan file output CSS Tailwind v4 diload di sini -->
    <link rel="stylesheet" href="./../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body class="bg-gray-100 antialiased text-gray-800">
    <!-- Kontainer Utama Mobile First -->
    <div class="w-full md:max-w-md mx-auto min-h-screen bg-page flex flex-col shadow-xl relative">

        <!-- App Bar / Header -->
        <header class="bg-primary-500 text-white px-5 py-4 flex items-center sticky top-0 z-20 shadow-sm">
            <a href="./../index.php" class="mr-4 hover:bg-primary-600 p-1 rounded transition-colors">
                <i class="fas fa-chevron-left text-lg"></i>
            </a>
            <h1 class="text-lg font-semibold tracking-wide"><?= $page_title ?></h1>
        </header>

        <!-- Main Content -->
        <main class="flex-1 px-5 py-6 overflow-y-auto">

            <div class="bg-primary-50 border border-primary-200 rounded p-4 flex gap-3 relative">
                <!-- Icon Kiri -->
                <div class="w-10 h-10 rounded-full bg-orange-100 text-orange-700 shrink-0 flex justify-center items-center mt-0.5">
                    <i class="fas fa-history text-lg"></i>
                </div>

                <!-- Konten Tengah & Kanan -->
                <div class="flex-1 flex flex-col">
                    <!-- Header Card (Judul & Unread Dot) -->
                    <div class="flex justify-between items-start gap-2">
                        <h3 class="font-bold text-gray-900 text-[15px] leading-snug">
                            Konfirmasi kehadiran besok
                        </h3>
                        <!-- Indikator Unread (Titik Teal) -->
                        <span class="w-2.5 h-2.5 bg-primary-600 rounded-full mt-1.5 shrink-0"></span>
                    </div>

                    <!-- Pesan Notifikasi -->
                    <p class="text-[13px] text-gray-600 mt-1 leading-relaxed">
                        pembersihan karang gigi dengan drg. Nadia Putri, Sp.KG, Kam 24 Sep, 14.00. Mohon Konfirmasi kehadiranmu
                    </p>

                    <!-- Footer Card (Waktu & Aksi - Layout Split) -->
                    <div class="flex justify-between items-center mt-3 text-xs">
                        <span class="text-gray-500">2 Jam lalu</span>

                        <!-- Tombol Aksi dengan Arrow -->
                        <a href="#" class="font-semibold text-primary-600 hover:underline flex items-center gap-1">
                            Konfirmasi sekarang
                            <i class="fas fa-chevron-right text-2xs"></i>
                        </a>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <script src="./../node_modules/flowbite/dist/flowbite.min.js"></script>
</body>

</html>