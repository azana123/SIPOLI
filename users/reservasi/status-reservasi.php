<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Reservasi | SIPOLI</title>
    <!-- Asumsi style.css berisi layer base Tailwind v4 dan Flowbite -->
    <link rel="stylesheet" href="./../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body class="bg-gray-100 antialiased text-gray-800">
    <!-- Kontainer Utama Mobile First -->
    <div class="w-full md:max-w-md mx-auto min-h-screen px-6 py-8 bg-page relative flex flex-col justify-center shadow-xl">

        <!-- Ikon Sukses -->
        <div class="flex justify-center mb-6 mt-4">
            <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center">
                <div class="w-16 h-16 bg-primary rounded-full flex items-center justify-center text-white text-3xl shadow-md">
                    <i class="fas fa-check"></i>
                </div>
            </div>
        </div>

        <!-- Teks Konfirmasi -->
        <div class="text-center mb-8">
            <h1 class="text-xl font-bold text-gray-900 mb-2">Reservasi berhasil</h1>
            <p class="text-sm text-gray-500 px-4">Reservasimu sudah tercatat dan menunggu konfirmasi dari poliklinik.</p>
        </div>

        <!-- Card Detail Reservasi -->
        <!-- Menggunakan class 'rounded' sesuai kesepakatan desain sistem -->
        <div class="bg-white rounded p-5 shadow-sm border border-gray-100">
            
            <!-- Row: Poli -->
            <div class="flex justify-between items-center py-3.5 border-b border-gray-100">
                <span class="text-sm font-semibold text-gray-500">Poli</span>
                <span class="text-sm font-bold text-gray-900"><?= $poliName ?? 'Poli Gigi' ?></span>
            </div>
            
            <!-- Row: Dokter -->
            <div class="flex justify-between items-center py-3.5 border-b border-gray-100">
                <span class="text-sm font-semibold text-gray-500">Dokter</span>
                <span class="text-sm font-bold text-gray-900"><?= $doctorName ?? 'drg. Nadia Putri, Sp.KG' ?></span>
            </div>
            
            <!-- Row: Tanggal -->
            <div class="flex justify-between items-center py-3.5 border-b border-gray-100">
                <span class="text-sm font-semibold text-gray-500">Tanggal</span>
                <span class="text-sm font-bold text-gray-900"><?= $selectedDate ?? 'Kam, 24 Sep 2026' ?></span>
            </div>
            
            <!-- Row: Waktu -->
            <div class="flex justify-between items-center py-3.5 border-b border-gray-100">
                <span class="text-sm font-semibold text-gray-500">Waktu</span>
                <span class="text-sm font-bold text-gray-900"><?= $selectedTime ?? '14.00' ?></span>
            </div>
            
            <!-- Row: Status -->
            <div class="flex justify-between items-center pt-3.5 pb-1">
                <span class="text-sm font-semibold text-gray-500">Status</span>
                <span class="text-xs font-bold text-[#b45309] bg-[#fef3c7] px-3 py-1 rounded">Menunggu</span>
            </div>
            
        </div>

        <!-- Tombol Aksi (Mengisi ruang bawah) -->
        <div class="mt-4 pb-4 flex flex-col gap-3">
            <!-- Primary Button -->
            <a href="./detail-reservasi.php" class="w-full py-2 bg-primary hover:bg-primary-700 text-white text-center font-semibold rounded-full shadow-sm transition-colors focus:ring-4 focus:ring-primary-300 block">
                Lihat reservasi saya
            </a>
            
            <!-- Secondary/Outline Button -->
            <a href="./../../index.php" class="w-full py-2 transparent border-2 border-primary-600 text-primary-600 hover:bg-primary-50 text-center font-semibold rounded-full transition-colors focus:ring-4 focus:ring-primary-100 block">
                Kembali ke beranda
            </a>
        </div>

    </div>

    <script src="./../../node_modules/flowbite/dist/flowbite.min.js"></script>
</body>

</html>