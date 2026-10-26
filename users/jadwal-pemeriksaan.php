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
                <a href="./../index.php" class="action-link text-page hover:text-primary-700 flex items-center gap-3">
                    <div class="text-xs">
                        <i class="fas fa-chevron-left"></i>
                    </div>
                    <span class="font-medium">Jadwal Dokter</span>
                </a>
            </section>
            <section class="content bg-page rounded-t-2xl py-3 px-4.5 w-full h-screen">
                <div class="category">
                    <ul class="flex justify-between md:justify-start md:gap-3">
                        <li>
                            <input type="radio" id="hosting-small" name="hosting" value="hosting-small" class="hidden peer" required />
                            <label for="hosting-small" class="inline-flex items-center bg-primary-500 justify-between text-sm py-3 px-4 rounded-full text-white bg-neutral-primary-soft border-default cursor-pointer peer-checked:hover:bg-brand-softer peer-checked:border-brand-subtle peer-checked:bg-brand-softer hover:bg-neutral-secondary-medium peer-checked:text-fg-brand-strong">
                                <div class="block">
                                    <div class="w-full font-semibold">Semua</div>
                                </div>
                            </label>
                        </li>
                        <li>
                            <input type="radio" id="umum" name="hosting" value="umum" class="hidden peer" required />
                            <label for="umum" class="inline-flex items-center justify-between text-sm py-3 px-4 rounded-full text-body bg-neutral-primary-soft border-1 border-default rounded-base cursor-pointer peer-checked:hover:bg-brand-softer peer-checked:border-brand-subtle peer-checked:bg-brand-softer hover:bg-neutral-secondary-medium peer-checked:text-fg-brand-strong">
                                <div class="block">
                                    <div class="w-full font-semibold">Umum</div>
                                </div>
                            </label>
                        </li>
                        <li>
                            <input type="radio" id="gigi" name="hosting" value="gigi" class="hidden peer" required />
                            <label for="gigi" class="inline-flex items-center justify-between text-sm py-3 px-4 rounded-full text-body bg-neutral-primary-soft border-1 border-default rounded-base cursor-pointer peer-checked:hover:bg-brand-softer peer-checked:border-brand-subtle peer-checked:bg-brand-softer hover:bg-neutral-secondary-medium peer-checked:text-fg-brand-strong">
                                <div class="block">
                                    <div class="w-full font-semibold">Gigi</div>
                                </div>
                            </label>
                        </li>
                        <li>
                            <input type="radio" id="konseling" name="hosting" value="konseling" class="hidden peer" required />
                            <label for="konseling" class="inline-flex items-center justify-between text-sm py-3 px-4 rounded-full text-body bg-neutral-primary-soft border-1 border-default rounded-base cursor-pointer peer-checked:hover:bg-brand-softer peer-checked:border-brand-subtle peer-checked:bg-brand-softer hover:bg-neutral-secondary-medium peer-checked:text-fg-brand-strong">
                                <div class="block">
                                    <div class="w-full font-semibold">Konseling</div>
                                </div>
                            </label>
                        </li>
                    </ul>
                </div>
                <h4 class="month my-3 text-xl font-bold">September 2026</h4>
                <div class="date-filter grid grid-cols-5 gap-3">
                    <a href="#" class="bg-primary-500 text-white rounded flex flex-col items-center justify-center p-1.5 text-sm">
                        <span class="day">Sen</span>
                        <span class="date">21</span>
                    </a>
                    <a href="#" class="bg-white rounded flex flex-col items-center justify-center p-1.5 text-sm">
                        <span class="day">Sel</span>
                        <span class="date">22</span>
                    </a>
                    <a href="#" class="bg-white rounded flex flex-col items-center justify-center p-1.5 text-sm">
                        <span class="day">Rab</span>
                        <span class="date">23</span>
                    </a>
                    <a href="#" class="bg-white rounded flex flex-col items-center justify-center p-1.5 text-sm">
                        <span class="day">Kam</span>
                        <span class="date">24</span>
                    </a>
                    <a href="#" class="bg-white rounded flex flex-col items-center justify-center p-1.5 text-sm">
                        <span class="day">Jum</span>
                        <span class="date">25</span>
                    </a>
                </div>
                <p class="date text-primary-700 font-medium my-3">Senin, 21 September</p>
                <div class="schedule md:grid md:grid-cols-3 md:gap-3">
                    <div class="bg-neutral-primary-soft w-full p-6 rounded-base flex flex-col gap-4 mb-4 md:mb-0">
                        <div class="flex gap-3">
                            <div class="icon w-12 h-12 rounded-full bg-primary-100 flex justify-center items-center">
                                <i class="far fa-calendar-alt text-primary text-2xl"></i>
                            </div>
                            <div class="desc">
                                <h6 class="font-bold">Konseling</h6>
                                <p class="doctor-name text-sm">Dr. Sarah Amelia</p>
                                <p class="date text-sm">Jum, 18 Sep. 14.00</p>
                            </div>
                        </div>
                        <a href="./users/jadwal-dokter.php" class="action-link text-page text-center rounded-full text-sm px-4 py-2.5 bg-primary-500 hover:bg-primary-700">
                            <span class="font-medium">Reservasi</span>
                        </a>
                    </div>
                    <div class="bg-neutral-primary-soft max-w-sm p-6 rounded-base flex flex-col gap-4">
                        <div class="flex gap-3">
                            <div class="icon w-12 h-12 rounded-full bg-primary-100 flex justify-center items-center">
                                <i class="far fa-calendar-alt text-primary text-2xl"></i>
                            </div>
                            <div class="desc flex-1">
                                <h6 class="font-bold">Konseling</h6>
                                <p class="doctor-name text-sm">Dr. Sarah Amelia</p>
                                <p class="date text-sm">Jum, 18 Sep. 14.00</p>
                            </div>
                            <div class="status-badge">
                                <span class="bg-danger-soft text-fg-danger text-xs font-medium px-2 py-1 rounded">Penuh 5/5</span>
                            </div>
                        </div>
                        <a href="./users/jadwal-dokter.php" class="action-link text-gray-300 bg-neutral-secondary-medium box-border border border-default-medium focus:ring-4 focus:ring-neutral-tertiary shadow-xs font-medium text-center rounded-full text-sm px-4 py-2.5 focus:outline-none">
                            <span class="font-medium">Kuota Penuh</span>
                        </a>
                    </div>
                </div>
            </section>
        </main>
    </div>
    <script src="./../../assets/js/app.js"></script>
    <script src="./../node_modules/flowbite/dist/flowbite.min.js"></script>
</body>

</html>