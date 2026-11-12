<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use PDOException;
use RuntimeException;

final class PoliRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function all(): array
    {
        return $this->pdo->query('SELECT * FROM poli ORDER BY nama_poli')->fetchAll();
    }

    /** Semua poli + jumlah pasiennya. LEFT JOIN supaya poli tanpa pasien tetap muncul. */
    public function allWithCount(): array
    {
        return $this->pdo->query(
            'SELECT po.*, COUNT(p.id) AS jumlah_pasien
             FROM poli po
             LEFT JOIN pasien p ON p.poli_id = po.id
             GROUP BY po.id
             ORDER BY po.nama_poli'
        )->fetchAll();
    }

    /** @return int[] daftar id poli yang valid (dipakai validator) */
    public function ids(): array
    {
        return array_map('intval', $this->pdo->query('SELECT id FROM poli')->fetchAll(PDO::FETCH_COLUMN));
    }

    public function find(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM poli WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function create(string $nama, int $tarif): void
    {
        $this->run('INSERT INTO poli (nama_poli, tarif) VALUES (:nama, :tarif)', [
            'nama' => $nama, 'tarif' => $tarif,
        ]);
    }

    public function update(int $id, string $nama, int $tarif): void
    {
        $this->run('UPDATE poli SET nama_poli = :nama, tarif = :tarif WHERE id = :id', [
            'nama' => $nama, 'tarif' => $tarif, 'id' => $id,
        ]);
    }

    public function delete(int $id): void
    {
        $this->run('DELETE FROM poli WHERE id = :id', ['id' => $id]);
    }

    /**
     * Jalankan query; terjemahkan error MySQL menjadi pesan yang ramah.
     * errorInfo[1] berisi kode error asli MySQL.
     */
    private function run(string $sql, array $params): void
    {
        try {
            $this->pdo->prepare($sql)->execute($params);
        } catch (PDOException $e) {
            throw match ((int) ($e->errorInfo[1] ?? 0)) {
                1062    => new RuntimeException('Nama poli sudah ada.'),
                1451    => new RuntimeException('Poli tidak bisa dihapus karena masih dipakai data pasien.'),
                default => $e,
            };
        }
    }
}
