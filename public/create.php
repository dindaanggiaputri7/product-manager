<?php
// public/create.php — CREATE + validasi + PRG
declare(strict_types=1);
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../config/helpers.php';

$errors = [];
$old = ['name' => '', 'category' => 'Umum', 'price' => '', 'stock' => '0'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    [$data, $errors, $old] = validate_product($pdo, $_POST);

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO products (name, category, price, stock)
                 VALUES (:name, :category, :price, :stock)'
            );
            $stmt->execute($data);
            redirect('index.php?status=created');   // PRG: cegah data ganda saat refresh
        } catch (PDOException $ex) {
            // 23000 = pelanggaran UNIQUE (dua request bersamaan dengan nama sama)
            if ($ex->getCode() === '23000') {
                $errors['name'] = 'Nama produk sudah digunakan.';
            } else {
                throw $ex;
            }
        }
    }
}

$title = 'Tambah Item';
$action = 'create.php';
$submitLabel = 'Simpan item';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-head"><div><h1>Tambah Item</h1><p class="muted">Daftarkan ramuan, gulungan, relik, atau bahan baru ke persediaan.</p></div></div>
<?php require __DIR__ . '/../includes/product_form.php'; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
