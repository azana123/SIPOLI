<?php
function get_base_url() {
    // 1. Cek protokol yang digunakan (http atau https)
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://";
    
    // 2. Ambil nama host (contoh: localhost atau www.domain.com)
    $host = $_SERVER['HTTP_HOST'];
    
    // 3. Ambil direktori path dari script yang sedang dieksekusi
    // Ini berguna jika project Anda berada di dalam sub-folder (contoh: localhost/SIPOLI)
    $path = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
    
    // Hilangkan slash di akhir jika ada, agar rapi
    $path = rtrim($path, '/');
    
    return $protocol . $host . $path;
}

// Definisikan sebagai konstanta agar bisa dipakai di mana saja
define('BASE_URL', get_base_url());
?>