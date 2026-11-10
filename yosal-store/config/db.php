<?php
// =====================================================
// Koneksi Database - Yosal Store
// Sesuaikan DB_USER / DB_PASS dengan konfigurasi MySQL kamu
// =====================================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'yosal_store');

$koneksi = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$koneksi) {
    die('Koneksi database gagal: ' . mysqli_connect_error());
}

mysqli_set_charset($koneksi, 'utf8mb4');

// Base URL project (sesuaikan jika folder project berbeda nama)
define('BASE_URL', '/yosal-store');
