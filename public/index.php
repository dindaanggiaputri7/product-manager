<?php
// public/index.php — READ (+ bonus search dengan GET)
declare(strict_types=1);
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../config/helpers.php';

// Pesan sukses dari pola PRG (whitelist supaya tidak bisa diisi sembarang teks)
$messages = [
    'created'  => ['ok',    'Item berhasil disimpan.'],
    'updated'  => ['ok',    'Item berhasil diperbarui.'],
    'deleted'  => ['ok',    'Item berhasil dihapus.'],
    'notfound' => ['error', 'Item tidak ditemukan.'],
];
$flash = $messages[$_GET['status'] ?? ''] ?? null;

// BONUS: search / filter — input GET tetap dikirim sebagai parameter SQL
$q = trim((string) ($_GET['q'] ?? ''));

if ($q !== '') {
    // Catatan: PDO dengan EMULATE_PREPARES=false tidak boleh memakai placeholder
    // bernama yang sama dua kali, jadi dipisah :q1 dan :q2.
    $stmt = $pdo->prepare(
        'SELECT id, name, category, price, stock FROM products
         WHERE name LIKE :q1 OR category LIKE :q2
         ORDER BY id DESC'
    );
    $stmt->execute(['q1' => "%$q%", 'q2' => "%$q%"]);
} else {
    // Query statis (tanpa input user) -> boleh pakai query()
    $stmt = $pdo->query('SELECT id, name, category, price, stock FROM products ORDER BY id DESC');
}
$products = $stmt->fetchAll();

// Kategori untuk chip filter (query statis)
$cats = $pdo->query(
    'SELECT category, COUNT(*) AS n FROM products GROUP BY category ORDER BY category'
)->fetchAll();

// Statistik ringkas (query statis)
$stats = $pdo->query(
    'SELECT COUNT(*) AS total,
            COALESCE(SUM(stock), 0)                 AS units,
            COALESCE(SUM(price * stock), 0)         AS worth,
            COALESCE(SUM(stock = 0), 0)             AS empty_stock,
            COALESCE(SUM(stock BETWEEN 1 AND 5), 0) AS low_stock
     FROM products'
)->fetch();
$needRestock = (int) $stats['empty_stock'] + (int) $stats['low_stock'];

$title = 'Persediaan';
require __DIR__ . '/../includes/header.php';
?>

<section class="head">
  <div>
    <h1>Persediaan Apotek</h1>
    <p class="muted">Pantau ramuan, gulungan sihir, relik, dan bahan langka.</p>
  </div>
  <form class="search" method="GET" action="index.php" role="search">
    <input name="q" placeholder="Cari nama atau kategori…" value="<?= e($q) ?>" aria-label="Cari item">
    <button class="btn btn--primary">Cari</button>
  </form>
</section>

<?php if ($flash): ?>
  <div class="alert alert--<?= e($flash[0]) ?>" role="status"><?= e($flash[1]) ?></div>
<?php endif; ?>

<section class="summary" aria-label="Ringkasan">
  <div class="summary__item"><span>Jenis item</span><b><?= (int) $stats['total'] ?></b></div>
  <div class="summary__item"><span>Total unit</span><b><?= number_format((int) $stats['units'], 0, ',', '.') ?></b></div>
  <div class="summary__item"><span>Nilai gudang</span><b><?= e(rupiah($stats['worth'])) ?></b></div>
  <div class="summary__item <?= $needRestock > 0 ? 'summary__item--warn' : '' ?>">
    <span>Perlu restock</span>
    <b><?= $needRestock ?></b>
    <small><?= (int) $stats['empty_stock'] ?> habis · <?= (int) $stats['low_stock'] ?> menipis</small>
  </div>
</section>

<?php if ($cats): ?>
  <nav class="chips" aria-label="Filter kategori">
    <a class="chip <?= $q === '' ? 'is-active' : '' ?>" href="index.php">Semua <small><?= (int) $stats['total'] ?></small></a>
    <?php foreach ($cats as $c): ?>
      <a class="chip <?= $q === $c['category'] ? 'is-active' : '' ?>" style="--h: <?= hue($c['category']) ?>"
         href="index.php?q=<?= urlencode($c['category']) ?>"><i class="dot"></i><?= e($c['category']) ?> <small><?= (int) $c['n'] ?></small></a>
    <?php endforeach; ?>
  </nav>
<?php endif; ?>

<?php if (!$products): ?>
  <div class="card empty">
    <?= $q !== '' ? 'Tidak ada item yang cocok dengan pencarian.' : 'Persediaan masih kosong. Klik “Tambah item” untuk mulai.' ?>
  </div>
<?php else: ?>
  <section class="products">
    <?php foreach ($products as $p): ?>
      <?php
        $stock = (int) $p['stock'];
        $level = min(100, (int) round($stock / 50 * 100));   // batang level stok (50 unit = penuh)
        $state = $stock === 0 ? 'out' : ($stock <= 5 ? 'low' : 'ok');
        $h     = hue($p['category']);
      ?>
      <article class="card product" style="--h: <?= $h ?>">
        <div class="product__art">
          <span class="badge"><?= e($p['category']) ?></span>
          <?= flask_svg($h) ?>
        </div>
        <div class="product__body">
          <h3><?= e($p['name']) ?></h3>
          <p class="price"><?= e(rupiah($p['price'])) ?></p>
          <div class="meter meter--<?= $state ?>" aria-hidden="true"><span style="width: <?= $level ?>%"></span></div>
          <p class="stock stock--<?= $state ?>">
            <?= $stock === 0 ? 'Stok habis' : 'Stok ' . $stock . ' unit' . ($stock <= 5 ? ' · menipis' : '') ?>
          </p>
          <div class="actions">
            <a class="btn btn--ghost" href="edit.php?id=<?= (int) $p['id'] ?>">Edit</a>
            <form method="POST" action="delete.php"
                  onsubmit="return confirm('Hapus item ini? Tindakan tidak bisa dibatalkan.');">
              <?= csrf_field() ?>
              <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
              <button class="btn btn--danger">Hapus</button>
            </form>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </section>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>
