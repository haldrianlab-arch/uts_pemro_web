<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Repositories\PoliRepository;
use RuntimeException;

final class PoliController
{
    public function __construct(private readonly PoliRepository $poli)
    {
    }

    /** Daftar poli + form tambah. Kalau ada ?edit=ID, form berubah jadi mode ubah. */
    public function index(array $errors = [], array $old = [], ?int $editId = null): void
    {
        $editId ??= (int) ($_GET['edit'] ?? 0); // ??= : isi hanya kalau masih null
        $editing  = $editId ? $this->poli->find($editId) : null;

        view('poli/index', [
            'title'   => 'Data Poli',
            'polis'   => $this->poli->allWithCount(),
            'editing' => $editing,
            'old'     => $old ?: ($editing ?? []),
            'errors'  => $errors,
        ]);
    }

    public function store(): void
    {
        $this->save(null);
    }

    public function update(int $id): void
    {
        $this->save($id);
    }

    public function destroy(int $id): void
    {
        try {
            $this->poli->delete($id);
            flash('success', 'Poli berhasil dihapus.');
        } catch (RuntimeException $e) {
            flash('error', $e->getMessage());
        }
        redirect('poli.index');
    }

    /** Dipakai store & update supaya validasi tidak ditulis 2x. */
    private function save(?int $id): void
    {
        $nama  = trim((string) ($_POST['nama_poli'] ?? ''));
        $tarif = filter_var($_POST['tarif'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);

        $errors = [];
        if ($nama === '' || mb_strlen($nama) > 50) {
            $errors['nama_poli'] = 'Nama poli wajib diisi (maks 50 karakter).';
        }
        if ($tarif === false) {
            $errors['tarif'] = 'Tarif harus bilangan bulat ≥ 0.';
        }

        if (!$errors) {
            try {
                $id === null
                    ? $this->poli->create($nama, $tarif)
                    : $this->poli->update($id, $nama, $tarif);

                flash('success', $id === null ? 'Poli berhasil ditambahkan.' : 'Poli berhasil diubah.');
                redirect('poli.index');
            } catch (RuntimeException $e) {
                $errors['nama_poli'] = $e->getMessage(); // contoh: "Nama poli sudah ada."
            }
        }

        http_response_code(422);
        $this->index($errors, ['nama_poli' => $nama, 'tarif' => $_POST['tarif'] ?? ''], $id ?? 0);
    }
}
