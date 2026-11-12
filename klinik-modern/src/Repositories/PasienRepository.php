<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Support\BiayaCalculator;
use PDO;

/**
 * REPOSITORY PATTERN
 * Semua query SQL untuk tabel `pasien` dikumpulkan di class ini.
 * Controller tidak menulis SQL sama sekali — cukup panggil $repo->find($id), dst.
 * Keuntungan: SQL tidak tersebar di banyak file, mudah dites & diganti.
 *
 * Semua query memakai PREPARED STATEMENT:
 *   $stmt = $pdo->prepare('... WHERE id = :id');
 *   $stmt->execute(['id' => $id]);
 * Nilai dikirim TERPISAH dari perintah SQL, jadi input user tidak mungkin
 * "berubah" menjadi perintah SQL (SQL Injection mustahil).
 */
final class PasienRepository
{
    // Constructor property promotion (PHP 8.0): deklarasi + assign properti sekaligus
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * Ambil data per halaman + filter.
     * @return array{items: array<int, array>, total: int}
     */
    public function paginate(array $filter, int $page, int $perPage): array
    {
        [$where, $params] = $this->buildWhere($filter);

        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM pasien p $where");
        $stmt->execute($params);
        $total = (int) $stmt->fetchColumn();

        $sql = "SELECT p.*, po.nama_poli, po.tarif
                FROM pasien p
                JOIN poli po ON po.id = p.poli_id
                $where
                ORDER BY p.tanggal_kunjungan DESC, p.id DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        // LIMIT/OFFSET harus di-bind sebagai INTEGER
        $stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue('offset', ($page - 1) * $perPage, PDO::PARAM_INT);
        $stmt->execute();

        return ['items' => $stmt->fetchAll(), 'total' => $total];
    }

    /**
     * Susun WHERE dinamis. Return array [string $where, array $params].
     * Nama parameter dibedakan (:q1, :q2, :q3) karena prepared statement asli MySQL
     * tidak mengizinkan 1 nama parameter dipakai berkali-kali.
     */
    private function buildWhere(array $filter): array
    {
        $conditions = [];
        $params     = [];

        if (($filter['q'] ?? '') !== '') {
            $conditions[] = '(p.nama LIKE :q1 OR p.no_registrasi LIKE :q2 OR p.email LIKE :q3)';
            $like = '%' . $filter['q'] . '%';
            $params += ['q1' => $like, 'q2' => $like, 'q3' => $like];
        }
        if (!empty($filter['poli_id'])) {
            $conditions[] = 'p.poli_id = :poli_id';
            $params['poli_id'] = (int) $filter['poli_id'];
        }
        if (!empty($filter['status'])) {
            $conditions[] = 'p.status = :status';
            $params['status'] = $filter['status'];
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        return [$where, $params];
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.*, po.nama_poli, po.tarif
             FROM pasien p JOIN poli po ON po.id = p.poli_id
             WHERE p.id = :id'
        );
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null; // fetch() return false kalau tidak ada -> ubah jadi null
    }

    public function create(array $data): int
    {
        $sql = 'INSERT INTO pasien
                    (no_registrasi, nama, umur, email, no_hp, pin, jenis_kelamin, gejala,
                     poli_id, deskripsi, bukti_bayar, tanggal_kunjungan)
                VALUES
                    (:no_registrasi, :nama, :umur, :email, :no_hp, :pin, :jenis_kelamin, :gejala,
                     :poli_id, :deskripsi, :bukti_bayar, :tanggal_kunjungan)';

        $this->pdo->prepare($sql)->execute([
            'no_registrasi'     => $this->generateNoRegistrasi(),
            'nama'              => $data['nama'],
            'umur'              => $data['umur'],
            'email'             => $data['email'],
            'no_hp'             => $data['no_hp'],
            'pin'               => password_hash($data['pin'], PASSWORD_DEFAULT),
            'jenis_kelamin'     => $data['jenis_kelamin'],
            'gejala'            => implode(',', $data['gejala']),
            'poli_id'           => $data['poli_id'],
            'deskripsi'         => $data['deskripsi'],
            'bukti_bayar'       => $data['bukti_bayar'],
            'tanggal_kunjungan' => $data['tanggal_kunjungan'],
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $fields = [
            'nama'              => $data['nama'],
            'umur'              => $data['umur'],
            'email'             => $data['email'],
            'no_hp'             => $data['no_hp'],
            'jenis_kelamin'     => $data['jenis_kelamin'],
            'gejala'            => implode(',', $data['gejala']),
            'poli_id'           => $data['poli_id'],
            'deskripsi'         => $data['deskripsi'],
            'tanggal_kunjungan' => $data['tanggal_kunjungan'],
            'status'            => $data['status'],
            'bukti_bayar'       => $data['bukti_bayar'],
        ];

        // PIN hanya diganti kalau diisi
        if ($data['pin'] !== '') {
            $fields['pin'] = password_hash($data['pin'], PASSWORD_DEFAULT);
        }

        // ['nama' => ..., 'umur' => ...]  ->  "nama = :nama, umur = :umur, ..."
        $set = implode(', ', array_map(fn (string $col) => "$col = :$col", array_keys($fields)));

        $this->pdo
            ->prepare("UPDATE pasien SET $set WHERE id = :id")
            ->execute($fields + ['id' => $id]);
    }

    public function delete(int $id): void
    {
        $this->pdo->prepare('DELETE FROM pasien WHERE id = :id')->execute(['id' => $id]);
    }

    /** Statistik dashboard: jumlah per status dalam 1 query (conditional aggregation). */
    public function stats(): array
    {
        $row = $this->pdo->query(
            "SELECT
                COUNT(*)                    AS total,
                SUM(status = 'Menunggu')    AS menunggu,
                SUM(status = 'Diperiksa')   AS diperiksa,
                SUM(status = 'Selesai')     AS selesai
             FROM pasien"
        )->fetch();

        // Hasil SUM bisa NULL kalau tabel kosong -> paksa jadi int
        $stats = array_map(fn ($v) => (int) $v, $row);

        // Pendapatan: biaya dihitung di PHP (BiayaCalculator), jadi dijumlahkan di PHP juga
        $selesai = $this->pdo->query(
            "SELECT p.umur, p.gejala, po.tarif
             FROM pasien p JOIN poli po ON po.id = p.poli_id
             WHERE p.status = 'Selesai'"
        )->fetchAll();
        $stats['pendapatan'] = array_sum(array_map(
            fn (array $r): int => BiayaCalculator::forRow($r)['total'],
            $selesai,
        ));

        return $stats;
    }

    /** Contoh: REG-20261112-A1B2C3 (random_bytes = acak yang aman secara kriptografi) */
    private function generateNoRegistrasi(): string
    {
        return 'REG-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
    }
}
