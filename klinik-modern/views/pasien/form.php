<?php
/**
 * SATU form untuk TAMBAH dan UBAH.
 *   $pasien === null  -> mode tambah (action ke pasien.store)
 *   $pasien berisi    -> mode ubah   (action ke pasien.update)
 * Di versi dosen, form ini ditulis 2x (tambah.php & ubah.php).
 *
 * @var ?array $pasien  @var array $old  @var array $errors  @var array $polis
 */

use App\Enums\Gejala;
use App\Enums\JenisKelamin;
use App\Enums\Status;

$isEdit = $pasien !== null;
$action = $isEdit ? url('pasien.update', ['id' => $pasien['id']]) : url('pasien.store');

// Helper kecil khusus template ini
$val     = fn (string $key): string => e($old[$key] ?? '');
$err     = fn (string $key): string => isset($errors[$key]) ? '<p class="field__error">' . e($errors[$key]) . '</p>' : '';
$invalid = fn (string $key): string => isset($errors[$key]) ? 'aria-invalid="true"' : '';
$gejalaDipilih = (array) ($old['gejala'] ?? []);
?>
<div class="page-head">
    <h1><?= e($title) ?></h1>
    <?php if ($isEdit): ?><span class="mono muted"><?= e($pasien['no_registrasi']) ?></span><?php endif; ?>
</div>

<?php if ($errors): ?>
    <div class="alert alert--error">Ada <?= count($errors) ?> isian yang perlu diperbaiki.</div>
<?php endif; ?>

<!-- Validasi HTML5 (required, min, pattern) = lapis pertama di browser.
     Validasi SERVER (PasienValidator) tetap jalan. Untuk melihatnya bekerja,
     tambahkan atribut novalidate pada <form> lewat Inspect Element lalu submit form kosong. -->
<form method="post" action="<?= $action ?>" enctype="multipart/form-data" class="card form">
    <?= csrf_field() ?>

    <div class="grid-2">
        <div class="field">
            <label for="nama">Nama</label>
            <input id="nama" name="nama" value="<?= $val('nama') ?>" required minlength="3" maxlength="50" <?= $invalid('nama') ?>>
            <?= $err('nama') ?>
        </div>

        <div class="field">
            <label for="umur">Umur</label>
            <input id="umur" name="umur" type="number" min="0" max="120" value="<?= $val('umur') ?>" required <?= $invalid('umur') ?>>
            <?= $err('umur') ?>
        </div>

        <div class="field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" maxlength="50" value="<?= $val('email') ?>" required <?= $invalid('email') ?>>
            <?= $err('email') ?>
        </div>

        <div class="field">
            <label for="no_hp">Nomor HP</label>
            <input id="no_hp" name="no_hp" type="tel" inputmode="numeric" pattern="\d{10,13}" maxlength="13"
                   placeholder="08xxxxxxxxxx" value="<?= $val('no_hp') ?>" required <?= $invalid('no_hp') ?>>
            <?= $err('no_hp') ?>
        </div>

        <div class="field">
            <label for="pin"><?= $isEdit ? 'PIN baru' : 'PIN' ?></label>
            <input id="pin" name="pin" type="password" inputmode="numeric" pattern="\d{4,6}" autocomplete="new-password"
                   placeholder="<?= $isEdit ? 'Kosongkan jika tidak diganti' : '4–6 digit angka' ?>"
                   <?= $isEdit ? '' : 'required' ?> <?= $invalid('pin') ?>>
            <?= $err('pin') ?>
        </div>
    </div>

    <fieldset class="field">
        <legend>Jenis kelamin</legend>
        <?php foreach (JenisKelamin::cases() as $jk): ?>
            <label class="check">
                <input type="radio" name="jenis_kelamin" value="<?= $jk->value ?>" required
                    <?= ($old['jenis_kelamin'] ?? '') === $jk->value ? 'checked' : '' ?>>
                <?= $jk->label() ?>
            </label>
        <?php endforeach; ?>
        <?= $err('jenis_kelamin') ?>
    </fieldset>

    <fieldset class="field">
        <legend>Gejala</legend>
        <?php foreach (Gejala::cases() as $g): ?>
            <label class="check">
                <input type="checkbox" name="gejala[]" value="<?= e($g->value) ?>"
                    <?= in_array($g->value, $gejalaDipilih, true) ? 'checked' : '' ?>>
                <?= e($g->value) ?>
            </label>
        <?php endforeach; ?>
        <?= $err('gejala') ?>
    </fieldset>

    <div class="grid-2">
        <div class="field">
            <label for="poli_id">Poli tujuan</label>
            <select id="poli_id" name="poli_id" required <?= $invalid('poli_id') ?>>
                <option value="">— Pilih poli —</option>
                <?php foreach ($polis as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= (int) ($old['poli_id'] ?? 0) === (int) $p['id'] ? 'selected' : '' ?>>
                        <?= e($p['nama_poli']) ?> (<?= rupiah($p['tarif']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <?= $err('poli_id') ?>
        </div>

        <div class="field">
            <label for="tanggal_kunjungan">Tanggal kunjungan</label>
            <input id="tanggal_kunjungan" name="tanggal_kunjungan" type="date" value="<?= $val('tanggal_kunjungan') ?>"
                   <?= $isEdit ? '' : 'min="' . date('Y-m-d') . '"' ?> required <?= $invalid('tanggal_kunjungan') ?>>
            <?= $err('tanggal_kunjungan') ?>
        </div>
    </div>

    <?php if ($isEdit): ?>
        <div class="field">
            <label for="status">Status</label>
            <select id="status" name="status">
                <?php foreach (Status::cases() as $s): ?>
                    <option value="<?= $s->value ?>" <?= ($old['status'] ?? '') === $s->value ? 'selected' : '' ?>><?= $s->value ?></option>
                <?php endforeach; ?>
            </select>
            <?= $err('status') ?>
        </div>
    <?php endif; ?>

    <div class="field">
        <label for="deskripsi">Deskripsi keluhan</label>
        <textarea id="deskripsi" name="deskripsi" rows="3" maxlength="500" <?= $invalid('deskripsi') ?>><?= $val('deskripsi') ?></textarea>
        <?= $err('deskripsi') ?>
    </div>

    <div class="field">
        <label for="bukti_bayar">Bukti bayar <span class="muted small">(JPG/PNG, maks 2 MB<?= $isEdit ? ', kosongkan jika tidak diganti' : '' ?>)</span></label>
        <?php if ($isEdit && $pasien['bukti_bayar']): ?>
            <img src="<?= e(config('upload.url') . '/' . $pasien['bukti_bayar']) ?>" alt="Bukti bayar saat ini" class="thumb">
        <?php endif; ?>
        <input id="bukti_bayar" name="bukti_bayar" type="file" accept="image/jpeg,image/png" <?= $isEdit ? '' : 'required' ?> <?= $invalid('bukti_bayar') ?>>
        <?= $err('bukti_bayar') ?>
    </div>

    <div class="form__actions">
        <button class="btn btn--primary"><?= $isEdit ? 'Simpan perubahan' : 'Kirim registrasi' ?></button>
        <a href="<?= $isEdit ? url('pasien.show', ['id' => $pasien['id']]) : url('pasien.index') ?>" class="btn btn--ghost">Batal</a>
    </div>
</form>
