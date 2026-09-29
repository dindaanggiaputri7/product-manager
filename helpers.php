<?php
// config/helpers.php — fungsi bantu: escape, CSRF, redirect, validasi
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Escape output HTML (pencegah XSS). */
function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * Hue stabil per kategori untuk warna aksen. Dibatasi 170-319 (teal → biru → ungu)
 * supaya tidak pernah merah/oranye, yang dipakai khusus untuk peringatan.
 */
function hue(string $text): int
{
    return 170 + abs(crc32(mb_strtolower($text))) % 150;
}

/** Format angka jadi Rupiah, mis. 150000 -> Rp 150.000 */
function rupiah(mixed $n): string
{
    return 'Rp ' . number_format((float) $n, 0, ',', '.');
}

/** Ikon botol ramuan (SVG inline), warna mengikuti hue kategori. */
function flask_svg(int $h): string
{
    $line = "hsl($h 45% 38%)";
    $fill = "hsl($h 70% 78%)";
    return '<svg class="flask" viewBox="0 0 64 64" width="56" height="56" aria-hidden="true">'
         . '<path d="M25 6h14v4h-3v14l14 24a6 6 0 0 1-5 9H19a6 6 0 0 1-5-9l14-24V10h-3z" '
         . 'fill="#fff" stroke="' . $line . '" stroke-width="2.5" stroke-linejoin="round"/>'
         . '<path d="M20 42h24l6 10a4 4 0 0 1-3.4 6H17.4A4 4 0 0 1 14 52z" fill="' . $fill . '"/>'
         . '<circle cx="28" cy="48" r="2" fill="#fff"/><circle cx="37" cy="52" r="1.5" fill="#fff"/>'
         . '</svg>';
}

/** Redirect lalu hentikan script (dipakai pada pola PRG). */
function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/** Token CSRF disimpan di session. */
function csrf_token(): string
{
    $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

/** Tolak request jika token form != token session. */
function csrf_verify(): void
{
    if (!hash_equals($_SESSION['csrf'] ?? '', (string) ($_POST['csrf'] ?? ''))) {
        http_response_code(403);
        exit('Token CSRF tidak valid.');
    }
}

/**
 * Normalisasi + validasi input produk (server-side).
 * Return: [data bersih bertipe, daftar error, nilai lama untuk isi ulang form]
 */
function validate_product(PDO $pdo, array $in, int $ignoreId = 0): array
{
    $name     = trim((string) ($in['name'] ?? ''));
    $category = trim((string) ($in['category'] ?? ''));
    $category = $category === '' ? 'Umum' : $category;
    $priceRaw = trim((string) ($in['price'] ?? ''));
    $stockRaw = trim((string) ($in['stock'] ?? ''));

    $price = filter_var($priceRaw, FILTER_VALIDATE_FLOAT);
    $stock = filter_var($stockRaw, FILTER_VALIDATE_INT);

    $errors = [];

    if (mb_strlen($name) < 3) {
        $errors['name'] = 'Nama minimal 3 karakter.';
    } elseif (mb_strlen($name) > 100) {
        $errors['name'] = 'Nama maksimal 100 karakter.';
    } else {
        // Nama harus unik (abaikan baris yang sedang diedit)
        $cek = $pdo->prepare('SELECT id FROM products WHERE name = :name AND id <> :id');
        $cek->execute(['name' => $name, 'id' => $ignoreId]);
        if ($cek->fetch()) {
            $errors['name'] = 'Nama produk sudah digunakan.';
        }
    }

    if (mb_strlen($category) > 50) {
        $errors['category'] = 'Kategori maksimal 50 karakter.';
    }

    if ($price === false || $price <= 0) {
        $errors['price'] = 'Harga harus berupa angka > 0.';
    } elseif ($price > 9999999999.99) {
        $errors['price'] = 'Harga terlalu besar.';
    }

    if ($stock === false || $stock < 0) {
        $errors['stock'] = 'Stok harus bilangan bulat dan tidak boleh negatif.';
    }

    return [
        ['name' => $name, 'category' => $category, 'price' => $price, 'stock' => $stock],
        $errors,
        ['name' => $name, 'category' => $category, 'price' => $priceRaw, 'stock' => $stockRaw],
    ];
}
