<?php
/** @var array $polis  @var ?array $editing  @var array $old  @var array $errors */

$isEdit = $editing !== null;
$action = $isEdit ? url('poli.update', ['id' => $editing['id']]) : url('poli.store');
?>
<div class="page-head">
    <h1>Data Poli</h1>
</div>

<form method="post" action="<?= $action ?>" class="card toolbar">
    <?= csrf_field() ?>
    <div class="field">
        <label for="nama_poli"><?= $isEdit ? 'Ubah nama poli' : 'Nama poli baru' ?></label>
        <input id="nama_poli" name="nama_poli" value="<?= e($old['nama_poli'] ?? '') ?>" required maxlength="50">
        <?php if (isset($errors['nama_poli'])): ?><p class="field__error"><?= e($errors['nama_poli']) ?></p><?php endif; ?>
    </div>
    <div class="field">
        <label for="tarif">Tarif (Rp)</label>
        <input id="tarif" name="tarif" type="number" min="0" value="<?= e($old['tarif'] ?? '') ?>" required>
        <?php if (isset($errors['tarif'])): ?><p class="field__error"><?= e($errors['tarif']) ?></p><?php endif; ?>
    </div>
    <div class="field field--end">
        <button class="btn btn--primary"><?= $isEdit ? 'Simpan' : '+ Tambah' ?></button>
        <?php if ($isEdit): ?><a href="<?= url('poli.index') ?>" class="btn btn--ghost">Batal</a><?php endif; ?>
    </div>
</form>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr><th>Nama poli</th><th>Tarif</th><th>Jumlah pasien</th><th class="text-right">Aksi</th></tr>
        </thead>
        <tbody>
            <?php foreach ($polis as $p): ?>
                <tr class="<?= $isEdit && (int) $editing['id'] === (int) $p['id'] ? 'is-editing' : '' ?>">
                    <td><?= e($p['nama_poli']) ?></td>
                    <td><?= rupiah($p['tarif']) ?></td>
                    <td><?= $p['jumlah_pasien'] ?></td>
                    <td class="actions">
                        <a href="<?= url('poli.index', ['edit' => $p['id']]) ?>" class="btn btn--sm">Ubah</a>
                        <form method="post" action="<?= url('poli.destroy', ['id' => $p['id']]) ?>"
                              onsubmit="return confirm(<?= e(json_encode('Hapus poli ' . $p['nama_poli'] . '?')) ?>)">
                            <?= csrf_field() ?>
                            <!-- Tombol dinonaktifkan kalau poli masih punya pasien (FK RESTRICT juga menjaga di database) -->
                            <button class="btn btn--sm btn--danger" <?= $p['jumlah_pasien'] > 0 ? 'disabled title="Masih dipakai pasien"' : '' ?>>Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
