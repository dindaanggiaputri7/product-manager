<?php
// config/db.php — koneksi PDO (dipakai oleh semua halaman)
declare(strict_types=1);

$host = 'localhost';
$db   = 'store_db';
$user = 'root';
$pass = '';   // XAMPP default kosong. Ubah jika MySQL kamu memakai password.

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    // Jangan tampilkan detail error (bisa membocorkan user/host) ke pengunjung.
    http_response_code(500);
    exit('Koneksi database gagal. Pastikan MySQL menyala dan database/store_db.sql sudah diimport.');
}
