<?php
/**
 * View daftar pasien.
 * Template hanya MENAMPILKAN data — tidak ada query, tidak ada logika bisnis.
 * Sintaks alternatif (foreach: ... endforeach;) lebih enak dibaca di tengah HTML
 * dibanding kurung kurawal { } yang terpisah-pisah.
 *
 * @var array $items  @var int $total  @var int $page  @var int $totalPages
 * @var array $filter @var array $polis @var array $stats
 */

use App\Enums\JenisKelamin;
use App\Enums\Status;
use App\Support\BiayaCalculator;

// Link pagination tetap membawa filter yang sedang aktif
$pageUrl = fn (int $p): string => url('pasien.index', array_filter($filter) + ['page' => $p]);
?>
<div class="page-head">
    <h1>Data Registrasi Pasien</h1>
    <a href="<?= url('pasien.create') ?>" class="btn btn--primary">+ Registrasi Baru</a>
</div>

<section class="stats">
    <div class="stat"><span class="stat__value"><?= $stats['total'] ?></span><span>Total pasien</span></div>
    <?php foreach (Status::cases() as $s): ?>
        <div class="stat">
            <span class="stat__value"><?= $stats[strtolower($s->value)] ?></span>
            <span class="<?= $s->badgeClass() ?>"><?= $s->value ?></span>
        </div>
    <?php endforeach; ?>
    <div class="stat"><span class="stat__value stat__value--sm"><?= rupiah($stats['pendapatan']) ?></span><span>Pendapatan (selesai)</span></div>
</section>

<form method="get" class="toolbar">
    <input type="hidden" name="r" value="pasien.index">
    <input type="search" name="q" value="<?= e($filter['q']) ?>" placeholder="Cari nama, no. registrasi, email…">
    <select name="poli_id">
        <option value="">Semua poli</option>
        <?php foreach ($polis as $p): ?>
            <option value="<?= $p['id'] ?>" <?= $filter['poli_id'] === (int) $p['id'] ? 'selected' : '' ?>><?= e($p['nama_poli']) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="status">
        <option value="">Semua status</option>
        <?php foreach (Status::cases() as $s): ?>
            <option value="<?= $s->value ?>" <?= $filter['status'] === $s->value ? 'selected' : '' ?>><?= $s->value ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn">Cari</button>
    <a href="<?= url('pasien.index') ?>" class="btn btn--ghost">Reset</a>
</form>

<p class="muted"><?= $total ?> data ditemukan</p>

<?php if (!$items): ?>
    <div class="empty">Belum ada data yang cocok.</div>
<?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>No. Registrasi</th>
                    <th>Nama</th>
                    <th>Umur</th>
                    <th>Gejala</th>
                    <th>Poli</th>
                    <th class="text-right">Biaya</th>
                    <th>Kunjungan</th>
                    <th>Status</th>
                    <th class="text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $row):
                    $status = Status::from($row['status']); ?>
                    <tr>
                        <td class="mono"><?= e($row['no_registrasi']) ?></td>
                        <td>
                            <a href="<?= url('pasien.show', ['id' => $row['id']]) ?>"><strong><?= e($row['nama']) ?></strong></a>
                            <div class="muted small"><?= JenisKelamin::from($row['jenis_kelamin'])->label() ?></div>
                        </td>
                        <td><?= $row['umur'] ?> <div class="muted small"><?= kategori_usia((int) $row['umur']) ?></div></td>
                        <td>
                            <?php foreach (explode(',', $row['gejala']) as $g): ?>
                                <span class="chip"><?= e($g) ?></span>
                            <?php endforeach; ?>
                        </td>
                        <td><?= e($row['nama_poli']) ?></td>
                        <td class="text-right"><?= rupiah(BiayaCalculator::forRow($row)['total']) ?></td>
                        <td><?= nama_hari($row['tanggal_kunjungan']) ?><div class="muted small"><?= tanggal($row['tanggal_kunjungan']) ?></div></td>
                        <td><span class="<?= $status->badgeClass() ?>"><?= $status->value ?></span></td>
                        <td class="actions">
                            <a href="<?= url('pasien.edit', ['id' => $row['id']]) ?>" class="btn btn--sm">Ubah</a>
                            <!-- Hapus = POST + CSRF token + konfirmasi -->
                            <form method="post" action="<?= url('pasien.destroy', ['id' => $row['id']]) ?>"
                                  onsubmit="return confirm(<?= e(json_encode('Hapus data ' . $row['nama'] . '?')) ?>)">
                                <?= csrf_field() ?>
                                <button class="btn btn--sm btn--danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($totalPages > 1): ?>
        <nav class="pagination" aria-label="Halaman">
            <?php if ($page > 1): ?><a href="<?= e($pageUrl($page - 1)) ?>">‹</a><?php endif; ?>
            <?php foreach (range(1, $totalPages) as $p): ?>
                <a href="<?= e($pageUrl($p)) ?>" class="<?= $p === $page ? 'is-active' : '' ?>"><?= $p ?></a>
            <?php endforeach; ?>
            <?php if ($page < $totalPages): ?><a href="<?= e($pageUrl($page + 1)) ?>">›</a><?php endif; ?>
        </nav>
    <?php endif; ?>
<?php endif; ?>
