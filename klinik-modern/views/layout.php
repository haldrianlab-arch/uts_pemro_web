<?php
/**
 * LAYOUT — kerangka HTML yang sama untuk semua halaman.
 * Isi halaman ($content) disisipkan oleh fungsi view() di helpers.php.
 * Dengan begini <head>, navbar, dan flash message cukup ditulis SEKALI.
 *
 * @var string $title
 * @var string $content
 */
$currentRoute = (string) ($_GET['r'] ?? 'pasien.index');
$isActive     = fn (string $prefix): string => str_starts_with($currentRoute, $prefix) ? 'is-active' : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Klinik') ?> · <?= e(config('app.name')) ?></title>
    <link rel="stylesheet" href="css/app.css">
</head>
<body>
    <header class="topbar">
        <a href="<?= url('pasien.index') ?>" class="brand">✚ <?= e(config('app.name')) ?></a>
        <nav>
            <a href="<?= url('pasien.index') ?>" class="<?= $currentRoute === 'pasien.index' ? 'is-active' : '' ?>">Pasien</a>
            <a href="<?= url('pasien.create') ?>" class="<?= $currentRoute === 'pasien.create' ? 'is-active' : '' ?>">+ Registrasi</a>
            <a href="<?= url('poli.index') ?>" class="<?= $isActive('poli') ?>">Poli</a>
        </nav>
    </header>

    <main class="container">
        <?php foreach (pull_flashes() as $flash): ?>
            <div class="alert alert--<?= e($flash['type']) ?>" role="status"><?= e($flash['message']) ?></div>
        <?php endforeach; ?>

        <?= $content ?>
    </main>
</body>
</html>
