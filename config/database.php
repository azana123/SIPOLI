<?php

$host    = getenv('DB_HOST') ?: 'localhost';
$port    = getenv('DB_PORT') ?: '5432';
$db      = getenv('DB_NAME') ?: 'db_sipoli';
$user    = getenv('DB_USER') ?: 'postgres';
$pass    = getenv('DB_PASSWORD') ?: 'postgres';
$sslmode = getenv('DB_SSLMODE') ?: 'prefer';

/* Tidak perlu menuliskan $stmt->fetch(PDO::FETCH_ASSOC) di setiap query. 
Cukup $stmt->fetch(), dan hasilnya langsung berupa array dengan index nama kolom. */
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false, // menggunakan prepared statement asli bawaan postgre
];

try {
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$db;sslmode=$sslmode",
        $user,
        $pass,
        $options // Pasang opsi di sini
    );

} catch (PDOException $e) {
    error_log("Database connection failed!: " . $e->getMessage());
    die("Database connection failed!.");
}

?>