<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIPOLI</title>
    <!-- style,css -->
    <link rel="stylesheet" href="./../assets/css/style.css">
    <!-- Google Icon CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.1.0/css/all.css" integrity="sha512-ajhUYg8JAATDFejqbeN7KbF2zyPbbqz04dgOLyGcYEk/MJD3V+HJhJLKvJ2VVlqrr4PwHeGTTWxbI+8teA7snw==" crossorigin="anonymous" referrerpolicy="no-referrer">
</head>

<body>
    <div class="w-full min-h-screen bg-primary-500">
        <main>
            <section class="w-full py-4 px-6 text-page">
                <a href="./auth/login.php">login</a>
            </section>
            <section class="content bg-page rounded-t-2xl py-3 px-4.5  h-screen">
                <div class="top-content flex justify-between">
                    <h4 class="font-bold text-lg">Jadwal Dokter</h4>
                    <a href="./users/jadwal-pemeriksaan.php" class="action-link text-primary-400 hover:text-primary-700">
                        <span class="font-medium">Lihat Semua</span>
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
                <section class="hero bg-base-200 bg-[url(/assets/img/bg-modal-hero.png)] bg-cover bg-no-repeat rounded-2xl my-2.5">
                    <div id="controls-carousel" class="relative w-full" data-carousel="static">
                        <!-- Carousel wrapper -->
                        <div class="relative h-56 overflow-hidden rounded-base md:h-96">
                            <!-- Item 1 -->
                            <div class="hidden duration-700 ease-in-out" data-carousel-item>
                                <img src="/docs/images/carousel/carousel-1.svg" class="absolute block w-full -translate-x-1/2 -translate-y-1/2 top-1/2 left-1/2" alt="...">
                            </div>
                            <!-- Item 2 -->
                            <div class="hidden duration-700 ease-in-out" data-carousel-item="active">
                                <img src="/docs/images/carousel/carousel-2.svg" class="absolute block w-full -translate-x-1/2 -translate-y-1/2 top-1/2 left-1/2" alt="...">
                            </div>
                            <!-- Item 3 -->
                            <div class="hidden duration-700 ease-in-out" data-carousel-item>
                                <img src="/docs/images/carousel/carousel-3.svg" class="absolute block w-full -translate-x-1/2 -translate-y-1/2 top-1/2 left-1/2" alt="...">
                            </div>
                            <!-- Item 4 -->
                            <div class="hidden duration-700 ease-in-out" data-carousel-item>
                                <img src="/docs/images/carousel/carousel-4.svg" class="absolute block w-full -translate-x-1/2 -translate-y-1/2 top-1/2 left-1/2" alt="...">
                            </div>
                            <!-- Item 5 -->
                            <div class="hidden duration-700 ease-in-out" data-carousel-item>
                                <img src="/docs/images/carousel/carousel-5.svg" class="absolute block w-full -translate-x-1/2 -translate-y-1/2 top-1/2 left-1/2" alt="...">
                            </div>
                        </div>
                        <!-- Slider controls -->
                        <button type="button" class="absolute top-0 start-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none" data-carousel-prev>
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-base bg-white/30 dark:bg-gray-800/30 group-hover:bg-white/50 dark:group-hover:bg-gray-800/60 group-focus:ring-4 group-focus:ring-white dark:group-focus:ring-gray-800/70 group-focus:outline-none">
                                <svg class="w-5 h-5 text-white rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m15 19-7-7 7-7" />
                                </svg>
                                <span class="sr-only">Previous</span>
                            </span>
                        </button>
                        <button type="button" class="absolute top-0 end-0 z-30 flex items-center justify-center h-full px-4 cursor-pointer group focus:outline-none" data-carousel-next>
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-base bg-white/30 dark:bg-gray-800/30 group-hover:bg-white/50 dark:group-hover:bg-gray-800/60 group-focus:ring-4 group-focus:ring-white dark:group-focus:ring-gray-800/70 group-focus:outline-none">
                                <svg class="w-5 h-5 text-white rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 5 7 7-7 7" />
                                </svg>
                                <span class="sr-only">Next</span>
                            </span>
                        </button>
                    </div>

                    <!-- <div class="hero-content lg:flex-row-reverse justify-between">
                        <div class="w-1/2">
                            <h1 class="text-2xl font-bold text-page">Konsultasi <span class="text-primary-100">Kesehatan</span></h1>
                            <p class="text-page py-2">
                                Konsultasi dengan dokter dengan mudah dan cepat
                            </p>
                            <button class="btn  btn-primary">Get Started</button>
                        </div>
                        <img
                            alt="Tailwind CSS hero component"
                            src="./"
                            class="rounded-lg shadow-2xl" width="20" />

                    </div> -->
                </section>
                <section class="category">
                    <h4 class="title font-bold text-lg">Kategori</h4>
                    <div class="grid grid-cols-3 gap-3 mt-3">
                        <a href="#" class="bg-neutral-primary-soft place-items-center block px-8 py-5 border border-primary rounded-base shadow-md/50 shadow-primary-400 hover:bg-neutral-secondary-medium">
                            <div class="icon">
                                <i class="fas fa-stethoscope text-4xl text-primary text-center"></i>
                            </div>
                            <h5 class="text-lg tracking-tight text-heading leading-8">Gigi</h5>
                        </a>
                        <a href="#" class="bg-neutral-primary-soft place-items-center block px-8 py-5 border border-primary rounded-base shadow-md/50 shadow-primary-400 hover:bg-neutral-secondary-medium">
                            <div class="icon">
                                <i class="fas fa-stethoscope text-4xl text-primary text-center"></i>
                            </div>
                            <h5 class="text-lg tracking-tight text-heading leading-8">Umum</h5>
                        </a>
                        <a href="#" class="bg-neutral-primary-soft place-items-center block px-8 py-5 border border-primary rounded-base shadow-md/50 shadow-primary-400 hover:bg-neutral-secondary-medium">
                            <div class="icon">
                                <i class="fas fa-stethoscope text-4xl text-primary text-center"></i>
                            </div>
                            <h5 class="text-lg tracking-tight text-heading leading-8">Konseling</h5>
                        </a>
                    </div>
                </section>
                <section class="schedule my-4.5">
                    <div class="title flex justify-between items-center">
                        <h4 class="font-bold text-lg">Jadwal Kontrol</h4>
                        <a href="#" class="action-link text-primary-400 transition hover:text-primary-700">
                            <span class="font-medium">Lihat Semua</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                    <div class="content mt-3">
                        <!-- <a href="#" class="bg-neutral-primary-soft block px-8 py-5 border border-primary rounded-base shadow-md/50 shadow-primary-400 hover:bg-neutral-secondary-medium">
                            <div class="icon">
                                <i class="fas fa-stethoscope text-4xl text-primary text-center"></i>
                            </div>
                            <h5 class="text-lg tracking-tight text-heading leading-8">Umum</h5>
                        </a> -->
                        <div class="bg-neutral-primary-soft max-w-sm p-6 border rounded-base border-primary shadow-md/50 shadow-primary-400 flex justify-between">
                            <div class="icon w-12 h-12 rounded-full bg-primary-100 flex justify-center items-center self-center">
                                <i class="far fa-calendar-alt text-primary text-2xl"></i>
                            </div>
                            <div class="desc">
                                <h6 class="font-bold text-xl">Konseling</h6>
                                <p class="doctor-name">Dr. Sarah Amelia</p>
                                <p class="date">Jum, 18 Sep. 14.00</p>
                            </div>
                            <div class="status-badge">
                                <span class="bg-warning-soft text-fg-warning text-sm font-medium px-2 py-1 rounded">Menunggu</span>
                            </div>
                        </div>
                    </div>
                </section>
                <section class="doctor-list">
                    <div class="title flex justify-between items-center">
                        <h4 class="font-bold text-lg">Our Doctor</h4>
                        <a href="#" class="action-link text-primary-400 transition hover:text-primary-700">
                            <span class="font-medium">Lihat Semua</span>
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </div>
                    <div class="content mt-3">
                        <!-- <a href="#" class="bg-neutral-primary-soft block px-8 py-5 border border-primary rounded-base shadow-md/50 shadow-primary-400 hover:bg-neutral-secondary-medium">
                            <div class="icon">
                                <i class="fas fa-stethoscope text-4xl text-primary text-center"></i>
                            </div>
                            <h5 class="text-lg tracking-tight text-heading leading-8">Umum</h5>
                        </a> -->
                        <div class="bg-neutral-primary-soft max-w-sm p-6 border border-primary shadow-md/50 shadow-primary-400 rounded-base shadow-xs flex justify-between">
                            <div class="w-16 self-center">
                                <img src="./assets/img/doc-pic.png" alt="">
                            </div>
                            <div class="desc">
                                <h6 class="font-bold text-xl">Konseling</h6>
                                <p class="doctor-name">Dr. Sarah Amelia</p>
                                <p class="date">Jum, 18 Sep. 14.00</p>
                            </div>
                            <a class="icon self-center text-primary">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </div>
                    </div>
                </section>
            </section>
        </main>
    </div>
    <script src="./../assets/js/app.js"></script>
    <script src="./node_modules/flowbite/dist/flowbite.min.js"></script>
</body>

</html>