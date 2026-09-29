<?php
// public/delete.php — DELETE (hanya POST + token CSRF)
declare(strict_types=1);
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../config/helpers.php';

// Menghapus lewat link GET berbahaya, jadi selain POST langsung ditolak.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method tidak diizinkan.');
}

csrf_verify();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if ($id === false || $id === null || $id < 1) {
    redirect('index.php?status=notfound');
}

$stmt = $pdo->prepare('DELETE FROM products WHERE id = :id');
$stmt->execute(['id' => $id]);

redirect($stmt->rowCount() > 0 ? 'index.php?status=deleted' : 'index.php?status=notfound');
