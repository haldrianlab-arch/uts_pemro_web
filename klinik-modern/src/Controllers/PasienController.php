<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Enums\Status;
use App\Repositories\PasienRepository;
use App\Repositories\PoliRepository;
use App\Support\FileUploader;
use App\Support\PasienValidator;
use Throwable;

/**
 * CONTROLLER = "pengatur lalu lintas".
 * Menerima request -> minta data ke Repository -> kirim ke View.
 * Controller TIDAK berisi SQL dan TIDAK berisi HTML.
 *
 * 7 method standar CRUD (nama mengikuti konvensi Laravel "resource controller"):
 *   index   GET   daftar data
 *   show    GET   detail 1 data
 *   create  GET   form tambah
 *   store   POST  simpan data baru
 *   edit    GET   form ubah
 *   update  POST  simpan perubahan
 *   destroy POST  hapus
 */
final class PasienController
{
    public function __construct(
        private readonly PasienRepository $pasien,
        private readonly PoliRepository $poli,
        private readonly FileUploader $uploader,
        private readonly int $perPage,
    ) {
    }

    public function index(): void
    {
        $filter = [
            'q'       => trim((string) ($_GET['q'] ?? '')),
            'poli_id' => (int) ($_GET['poli_id'] ?? 0),
            'status'  => Status::tryFrom((string) ($_GET['status'] ?? ''))?->value,
        ];
        $page   = max(1, (int) ($_GET['page'] ?? 1));
        $result = $this->pasien->paginate($filter, $page, $this->perPage);

        view('pasien/index', [
            'title'      => 'Data Pasien',
            'items'      => $result['items'],
            'total'      => $result['total'],
            'page'       => $page,
            'totalPages' => max(1, (int) ceil($result['total'] / $this->perPage)),
            'filter'     => $filter,
            'polis'      => $this->poli->all(),
            'stats'      => $this->pasien->stats(),
        ]);
    }

    public function show(int $id): void
    {
        view('pasien/show', [
            'title'  => 'Detail Pasien',
            'pasien' => $this->findOrFail($id),
        ]);
    }

    public function create(): void
    {
        $this->renderForm(null, ['tanggal_kunjungan' => date('Y-m-d')]);
    }

    public function store(): void
    {
        [$data, $errors] = PasienValidator::validate($_POST, $this->poli->ids());

        $file = $_FILES['bukti_bayar'] ?? null;
        if ($fileError = $this->uploader->validate($file, required: true)) { // named argument (PHP 8)
            $errors['bukti_bayar'] = $fileError;
        }

        if ($errors) {
            http_response_code(422); // 422 Unprocessable Entity = data tidak valid
            $this->renderForm(null, $_POST, $errors);
            return;
        }

        $data['bukti_bayar'] = $this->uploader->store($file);

        try {
            $id = $this->pasien->create($data);
        } catch (Throwable $e) {
            $this->uploader->delete($data['bukti_bayar']); // rollback file kalau INSERT gagal
            throw $e;
        }

        flash('success', 'Registrasi berhasil disimpan.');
        redirect('pasien.show', ['id' => $id]); // PRG: Post -> Redirect -> Get
    }

    public function edit(int $id): void
    {
        $pasien = $this->findOrFail($id);
        $old    = $pasien;
        $old['gejala'] = explode(',', $pasien['gejala']);

        $this->renderForm($pasien, $old);
    }

    public function update(int $id): void
    {
        $pasien = $this->findOrFail($id);

        [$data, $errors] = PasienValidator::validate($_POST, $this->poli->ids(), isUpdate: true);

        $file = $_FILES['bukti_bayar'] ?? null;
        if ($fileError = $this->uploader->validate($file, required: false)) {
            $errors['bukti_bayar'] = $fileError;
        }

        if ($errors) {
            http_response_code(422);
            $this->renderForm($pasien, $_POST, $errors);
            return;
        }

        $newFile             = FileUploader::hasFile($file) ? $this->uploader->store($file) : null;
        $data['bukti_bayar'] = $newFile ?? $pasien['bukti_bayar'];

        try {
            $this->pasien->update($id, $data);
        } catch (Throwable $e) {
            $this->uploader->delete($newFile);
            throw $e;
        }

        if ($newFile) {
            $this->uploader->delete($pasien['bukti_bayar']); // buang file lama
        }

        flash('success', 'Data pasien berhasil diperbarui.');
        redirect('pasien.show', ['id' => $id]);
    }

    public function destroy(int $id): void
    {
        $pasien = $this->findOrFail($id);

        $this->pasien->delete($id);
        $this->uploader->delete($pasien['bukti_bayar']);

        flash('success', "Data {$pasien['nama']} berhasil dihapus.");
        redirect('pasien.index');
    }

    // -----------------------------------------------------------------

    private function findOrFail(int $id): array
    {
        return $this->pasien->find($id) ?? abort(404, 'Data pasien tidak ditemukan.');
    }

    /** Form tambah & ubah memakai SATU template yang sama (pasien = null berarti tambah). */
    private function renderForm(?array $pasien, array $old, array $errors = []): void
    {
        view('pasien/form', [
            'title'  => $pasien ? 'Ubah Data Pasien' : 'Registrasi Pasien',
            'pasien' => $pasien,
            'old'    => $old,
            'errors' => $errors,
            'polis'  => $this->poli->all(),
        ]);
    }
}
