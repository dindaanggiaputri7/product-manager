<?php
/* includes/header.php — $title diset oleh halaman sebelum include */
$current = basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title ?? 'Apotek Arcana') ?> · Apotek Arcana</title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
  <div class="wrap topbar__inner">
    <a class="logo" href="index.php">
      <?= flask_svg(262) ?>
      <span class="logo__text"><b>Apotek Arcana</b><small>Ramuan · Gulungan · Relik</small></span>
    </a>
    <nav class="topnav">
      <a class="topnav__link <?= $current === 'index.php' ? 'is-active' : '' ?>" href="index.php">Persediaan</a>
      <a class="btn btn--primary" href="create.php">+ Tambah item</a>
    </nav>
  </div>
</header>
<main class="wrap main">
