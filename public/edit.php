<?php
// public/edit.php — READ one (SELECT by ID) + UPDATE
declare(strict_types=1);
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../config/helpers.php';

// id dari POST (saat submit) atau GET (saat pertama dibuka); wajib integer valid
$id = filter_var($_POST['id'] ?? $_GET['id'] ?? null, FILTER_VALIDATE_INT);
if ($id === false || $id === null || $id < 1) {
    redirect('index.php?status=notfound');
}

// SELECT by ID -> prepared statement
$stmt = $pdo->prepare('SELECT id, name, category, price, stock FROM products WHERE id = :id');
$stmt->execute(['id' => $id]);
$product = $stmt->fetch();
if (!$product) {
    redirect('index.php?status=notfound');
}

$errors = [];
$old = [
    'name'     => $product['name'],
    'category' => $product['category'],
    'price'    => $product['price'],
    'stock'    => $product['stock'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    [$data, $errors, $old] = validate_product($pdo, $_POST, $id);

    if (empty($errors)) {
        try {
            $upd = $pdo->prepare(
                'UPDATE products
                 SET name = :name, category = :category, price = :price, stock = :stock
                 WHERE id = :id'
            );
            $upd->execute($data + ['id' => $id]);
            redirect('index.php?status=updated');
        } catch (PDOException $ex) {
            if ($ex->getCode() === '23000') {
                $errors['name'] = 'Nama produk sudah digunakan.';
            } else {
                throw $ex;
            }
        }
    }
}

$title = 'Edit Item';
$action = 'edit.php';
$submitLabel = 'Simpan perubahan';
require __DIR__ . '/../includes/header.php';
?>
<div class="page-head"><div><h1>Edit Item</h1><p class="muted">Ubah data item lalu simpan perubahan.</p></div></div>
<?php require __DIR__ . '/../includes/product_form.php'; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
