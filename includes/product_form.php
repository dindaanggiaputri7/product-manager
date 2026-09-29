<?php
/*
 * includes/product_form.php — form dipakai bersama oleh create.php & edit.php
 * Variabel yang dibutuhkan: $old, $errors, $action, $submitLabel, $id (opsional)
 */
$catList = array_unique(array_merge(
    ['Ramuan', 'Gulungan', 'Relik', 'Bahan Langka'],
    array_column($pdo->query('SELECT DISTINCT category FROM products')->fetchAll(), 'category')
));
$err = fn(string $f) => isset($errors[$f]) ? '<small class="error">' . e($errors[$f]) . '</small>' : '';
?>
<form class="card form" action="<?= e($action) ?>" method="POST" novalidate>
  <?= csrf_field() ?>
  <?php if (!empty($id)): ?>
    <input type="hidden" name="id" value="<?= (int) $id ?>">
  <?php endif; ?>

  <div class="field">
    <label for="name">Nama item</label>
    <input id="name" name="name" minlength="3" maxlength="100" required
           value="<?= e($old['name']) ?>" class="<?= isset($errors['name']) ? 'is-invalid' : '' ?>">
    <?= $err('name') ?>
  </div>

  <div class="field">
    <label for="category">Kategori</label>
    <input id="category" name="category" maxlength="50" list="category-list"
           value="<?= e($old['category']) ?>" class="<?= isset($errors['category']) ? 'is-invalid' : '' ?>">
    <datalist id="category-list">
      <?php foreach ($catList as $c): ?><option value="<?= e($c) ?>"><?php endforeach; ?>
    </datalist>
    <?= $err('category') ?>
  </div>

  <div class="field-row">
    <div class="field">
      <label for="price">Harga (Rp)</label>
      <input id="price" name="price" type="number" min="0.01" step="0.01" required
             value="<?= e($old['price']) ?>" class="<?= isset($errors['price']) ? 'is-invalid' : '' ?>">
      <?= $err('price') ?>
    </div>
    <div class="field">
      <label for="stock">Stok</label>
      <input id="stock" name="stock" type="number" min="0" step="1" required
             value="<?= e($old['stock']) ?>" class="<?= isset($errors['stock']) ? 'is-invalid' : '' ?>">
      <?= $err('stock') ?>
    </div>
  </div>

  <div class="actions">
    <button type="submit" class="btn btn--primary"><?= e($submitLabel) ?></button>
    <a class="btn btn--ghost" href="index.php">Batal</a>
  </div>
</form>
