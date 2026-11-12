<?php /** @var int $code  @var string $message */ ?>
<div class="card empty">
    <h1><?= $code ?></h1>
    <p><?= e($message) ?></p>
    <a href="<?= url('pasien.index') ?>" class="btn">Kembali ke beranda</a>
</div>
