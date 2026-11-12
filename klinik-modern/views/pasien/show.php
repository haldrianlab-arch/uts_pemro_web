<?php
/** @var array $pasien */

use App\Enums\JenisKelamin;
use App\Enums\Status;
use App\Support\BiayaCalculator;

$status = Status::from($pasien['status']);
$biaya  = BiayaCalculator::forRow($pasien);
?>
<div class="page-head">
    <div class="profile">
        <div class="avatar"><?= e(mb_strtoupper(mb_substr($pasien['nama'], 0, 1))) ?></div>
        <div>
            <h1><?= e($pasien['nama']) ?></h1>
            <span class="mono muted"><?= e($pasien['no_registrasi']) ?></span>
            <span class="<?= $status->badgeClass() ?>"><?= $status->value ?></span>
        </div>
    </div>
    <div class="actions">
        <a href="<?= url('pasien.edit', ['id' => $pasien['id']]) ?>" class="btn">Ubah</a>
        <form method="post" action="<?= url('pasien.destroy', ['id' => $pasien['id']]) ?>"
              onsubmit="return confirm('Hapus data ini? Tindakan tidak bisa dibatalkan.')">
            <?= csrf_field() ?>
            <button class="btn btn--danger">Hapus</button>
        </form>
    </div>
</div>

<div class="detail">
    <dl class="card dl">
        <dt>Umur</dt>          <dd><?= $pasien['umur'] ?> tahun · <?= kategori_usia((int) $pasien['umur']) ?></dd>
        <dt>Email</dt>         <dd><?= e($pasien['email']) ?></dd>
        <dt>Nomor HP</dt>      <dd><?= e($pasien['no_hp']) ?></dd>
        <dt>PIN</dt>           <dd>•••••• <span class="muted small">(disimpan sebagai hash bcrypt)</span></dd>
        <dt>Jenis kelamin</dt> <dd><?= JenisKelamin::from($pasien['jenis_kelamin'])->label() ?></dd>
        <dt>Gejala</dt>
        <dd><?php foreach (explode(',', $pasien['gejala']) as $g): ?><span class="chip"><?= e($g) ?></span><?php endforeach; ?></dd>
        <dt>Poli</dt>          <dd><?= e($pasien['nama_poli']) ?></dd>
        <dt>Rincian biaya</dt>
        <dd>
            <table class="bill">
                <tr><td>Biaya pokok (poli)</td><td><?= rupiah($biaya['biaya_pokok']) ?></td></tr>
                <tr><td>Pemeriksaan gejala</td><td><?= rupiah($biaya['biaya_gejala']) ?></td></tr>
                <tr><td>Diskon <?= kategori_usia((int) $pasien['umur']) ?> (<?= $biaya['persen'] ?>%)</td><td>− <?= rupiah($biaya['diskon']) ?></td></tr>
                <tr class="bill__total"><td>Total</td><td><?= rupiah($biaya['total']) ?></td></tr>
            </table>
        </dd>
        <dt>Kunjungan</dt>     <dd><?= nama_hari($pasien['tanggal_kunjungan']) ?>, <?= tanggal($pasien['tanggal_kunjungan']) ?></dd>
        <dt>Deskripsi</dt>     <dd><?= nl2br(e($pasien['deskripsi'] ?: '—')) ?></dd>
        <dt>Didaftarkan</dt>   <dd><?= tanggal($pasien['created_at'], 'd-m-Y H:i') ?></dd>
    </dl>

    <figure class="card proof">
        <figcaption>Bukti bayar</figcaption>
        <?php if ($pasien['bukti_bayar']): ?>
            <?php $src = e(config('upload.url') . '/' . $pasien['bukti_bayar']); ?>
            <a href="<?= $src ?>" target="_blank" rel="noopener"><img src="<?= $src ?>" alt="Bukti bayar"></a>
        <?php else: ?>
            <p class="muted">Belum ada file.</p>
        <?php endif; ?>
    </figure>
</div>

<p><a href="<?= url('pasien.index') ?>">← Kembali ke daftar</a></p>
