<?php
// Inisialisasi data referensi (Bisa dihubungkan ke database SIPOLI)
$page_title = "Form Reservasi";
$doctorName = "drg. Nadia Putri, Sp.KG";
$doctorSpecialist = "Spesialis Konservasi Gigi";
$selectedDate = "24, Sep, 2026";
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> | SIPOLI</title>
    <!-- Asumsi style.css berisi layer base Tailwind v4 dan Flowbite -->
    <link rel="stylesheet" href="./../../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body class="bg-gray-100 antialiased text-gray-800">
    <!-- Kontainer Utama Mobile First -->
    <div class="w-full mx-auto min-h-screen px-6 py-4 bg-page relative flex flex-col shadow-xl">

        <!-- Header Navigasi -->
        <header class="py-4 mb-2">
            <a href="./jadwal-reservasi.php" class="text-xl text-primary-700 hover:text-primary-800 transition-colors">
                <i class="fas fa-chevron-left"></i>
            </a>
        </header>

        <!-- Card 1: Profil Dokter -->
        <section class="bg-primary-100 rounded-xl p-4 flex items-center gap-4 mb-4 shadow-sm border border-primary-50">
            <!-- Skeleton Placeholder Gambar Profil -->
            <div class="w-14 h-14 rounded-full bg-white shrink-0 overflow-hidden relative border border-white shadow-sm">
                <!-- Tambahkan path gambar asli di atribut src di bawah -->
                <img src="" alt="<?= $doctorName ?>" class="absolute inset-0 w-full h-full object-cover">
                <div class="absolute inset-0 flex items-center justify-center text-primary-300 text-2xs text-center p-1 font-mono leading-tight">
                    [Img]
                </div>
            </div>
            <div>
                <h2 class="text-sm font-bold text-gray-900"><?= $doctorName ?></h2>
                <p class="text-xs text-gray-500 mt-0.5"><?= $doctorSpecialist ?></p>
            </div>
        </section>

        <!-- Card 2: Tanggal Dipilih -->
        <section class="bg-primary-100 rounded-xl p-4 flex items-center gap-4 mb-6 shadow-sm border border-primary-50">
            <div class="w-10 h-10 rounded text-primary text-xl flex items-center justify-center shrink-0">
                <i class="far fa-calendar-alt"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-0.5">Tanggal</p>
                <p class="text-sm font-bold text-gray-900"><?= $selectedDate ?></p>
            </div>
        </section>

        <!-- Card 3: Form Data Pasien -->
        <section class="bg-primary-100 rounded-xl p-5 flex-1 shadow-sm mb-6 border border-primary-50">
            <!-- Header Form -->
            <div class="flex items-center gap-3 mb-6 text-primary">
                <i class="fas fa-user text-lg"></i>
                <h3 class="font-bold text-base">Data Pasien</h3>
            </div>

            <!-- Form Reservasi -->
            <form id="formReservasi" action="status-reservasi.php" method="GET" class="space-y-5">

                <!-- Input Nama -->
                <div>
                    <label for="nama" class="flex items-center gap-2 mb-2 text-sm font-medium text-gray-500">
                        <i class="far fa-user text-primary w-4 text-center"></i> Nama
                    </label>
                    <input type="text" id="nama" name="nama" class="bg-page border-0 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-3 shadow-inner" placeholder="Nama Lengkap" required>
                </div>

                <!-- Input NIM (Spesifik Konteks Kampus) -->
                <div>
                    <label for="nim" class="flex items-center gap-2 mb-2 text-sm font-medium text-gray-500">
                        <i class="far fa-id-badge text-primary w-4 text-center"></i> NIM
                    </label>
                    <input type="number" id="nim" name="nim" class="bg-page border-0 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-3 shadow-inner" placeholder="Nomor Induk Mahasiswa" required>
                </div>

                <!-- Input Jurusan -->
                <div>
                    <label for="jurusan" class="flex items-center gap-2 mb-2 text-sm font-medium text-gray-500">
                        <i class="fas fa-users text-primary w-4 text-center"></i> Jurusan
                    </label>
                    <input type="text" id="jurusan" name="jurusan" class="bg-page border-0 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-3 shadow-inner" placeholder="Contoh: Teknik Informatika" required>
                </div>

                <!-- Input Keluhan -->
                <div>
                    <label for="keluhan" class="flex items-center gap-2 mb-2 text-sm font-medium text-gray-500">
                        <i class="fas fa-comment-medical text-primary w-4 text-center"></i> Keluhan
                    </label>
                    <textarea id="keluhan" name="keluhan" rows="2" class="bg-page border-0 text-gray-900 text-sm rounded focus:ring-primary-500 focus:border-primary-500 block w-full p-3 shadow-inner resize-none" placeholder="Deskripsikan keluhan Anda" required></textarea>
                </div>
            </form>
        </section>

        <div class="mt-auto pb-6">
            <button type="button" onclick="submitForm()" class="w-full py-2 bg-primary hover:bg-primary-700 text-white text-center font-semibold rounded-full shadow-lg transition-colors focus:ring-4 focus:ring-primary-300">
                Reservasi
            </button>
        </div>

    </div>

    <script>
        function submitForm() {
            const form = document.getElementById('formReservasi');

            // Memeriksa apakah semua input yang 'required' sudah diisi
            if (form.checkValidity()) {
                // Jika validasi lolos, submit form ke action url
                form.submit();
            } else {
                // Jika ada yang kosong, tampilkan peringatan bawaan browser
                form.reportValidity();
            }
        }
    </script>
    <script src="./../../node_modules/flowbite/dist/flowbite.min.js"></script>
</body>

</html>