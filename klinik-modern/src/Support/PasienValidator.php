<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\Gejala;
use App\Enums\JenisKelamin;
use App\Enums\Status;
use DateTimeImmutable;

/**
 * Validasi + normalisasi input form pasien.
 *
 * Bedanya dengan versi dosen:
 *  - Error disimpan PER FIELD  ['email' => 'Format email tidak valid.']
 *    sehingga pesan bisa ditampilkan tepat di bawah input yang salah.
 *  - Mengembalikan data yang sudah "bersih" ($clean) dengan tipe data benar
 *    (umur jadi int, gejala jadi array, dst) -> controller tinggal pakai.
 */
final class PasienValidator
{
    /**
     * @param int[] $poliIds  id poli yang valid
     * @return array{0: array, 1: array<string,string>}  [$clean, $errors]
     */
    public static function validate(array $input, array $poliIds, bool $isUpdate = false): array
    {
        $errors = [];

        // ?? (null coalescing) = pakai '' kalau key tidak ada. Pengganti isset() ? : .
        // preg_replace('/\s+/', ' ') = rapikan spasi ganda di tengah nama
        $nama      = preg_replace('/\s+/', ' ', trim((string) ($input['nama'] ?? '')));
        $umurRaw   = trim((string) ($input['umur'] ?? ''));
        $email     = strtolower(trim((string) ($input['email'] ?? '')));
        $noHp      = trim((string) ($input['no_hp'] ?? ''));
        $pin       = trim((string) ($input['pin'] ?? ''));
        $deskripsi = trim((string) ($input['deskripsi'] ?? ''));
        $tanggal   = (string) ($input['tanggal_kunjungan'] ?? '');
        $gejala    = (array) ($input['gejala'] ?? []);

        // --- Nama: \p{L} = huruf dari bahasa apa pun (é, ñ, dll), plus spasi . ' -
        $panjangNama = mb_strlen($nama);
        if ($nama === '') {
            $errors['nama'] = 'Nama wajib diisi.';
        } elseif ($panjangNama < 3 || $panjangNama > 50) {
            $errors['nama'] = 'Nama harus 3–50 karakter.';
        } elseif (!preg_match("/^[\p{L}\s.'-]+$/u", $nama)) {
            $errors['nama'] = 'Nama hanya boleh berisi huruf, spasi, titik, petik, atau strip.';
        }

        // --- Umur: filter_var dengan opsi range -> sekaligus cek "bilangan bulat" & batas
        $umur = filter_var($umurRaw, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 120]]);
        if ($umurRaw === '') {
            $errors['umur'] = 'Umur wajib diisi.';
        } elseif ($umur === false) {
            $errors['umur'] = 'Umur harus bilangan bulat 0–120.';
        }

        // --- Email
        if ($email === '') {
            $errors['email'] = 'Email wajib diisi.';
        } elseif (strlen($email) > 50 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Format email tidak valid (maks 50 karakter).';
        }

        // --- Nomor HP: 10-13 digit. \d{10,13} sekaligus cek "hanya angka" & panjang
        if ($noHp === '') {
            $errors['no_hp'] = 'Nomor HP wajib diisi.';
        } elseif (!preg_match('/^\d{10,13}$/', $noHp)) {
            $errors['no_hp'] = 'Nomor HP harus 10–13 digit angka.';
        }

        // --- PIN: wajib saat tambah, opsional saat ubah
        if ($pin === '' && !$isUpdate) {
            $errors['pin'] = 'PIN wajib diisi.';
        } elseif ($pin !== '' && !preg_match('/^\d{4,6}$/', $pin)) {
            $errors['pin'] = 'PIN harus 4–6 digit angka.';
        }

        // --- Jenis kelamin: tryFrom() return null kalau nilai tidak ada di enum
        $jk = JenisKelamin::tryFrom((string) ($input['jenis_kelamin'] ?? ''));
        if ($jk === null) {
            $errors['jenis_kelamin'] = 'Jenis kelamin wajib dipilih.';
        }

        // --- Gejala: buang nilai yang tidak dikenal, lalu cek minimal 1
        $gejala = array_values(array_unique(array_intersect($gejala, Gejala::values())));
        if (count($gejala) === 0) {
            $errors['gejala'] = 'Pilih minimal 1 gejala.';
        }

        // --- Poli
        $poliId = (int) ($input['poli_id'] ?? 0);
        if (!in_array($poliId, $poliIds, true)) {
            $errors['poli_id'] = 'Poli tujuan wajib dipilih.';
        }

        // --- Deskripsi (mb_strlen menghitung karakter, bukan byte)
        if (mb_strlen($deskripsi) > 500) {
            $errors['deskripsi'] = 'Deskripsi maksimal 500 karakter.';
        }

        // --- Tanggal: createFromFormat + bandingkan ulang = cek tanggal benar-benar valid
        //     (strtotime('2026-02-31') TIDAK error, tapi cara ini menolaknya)
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $tanggal);
        if ($date === false || $date->format('Y-m-d') !== $tanggal) {
            $errors['tanggal_kunjungan'] = 'Tanggal kunjungan tidak valid.';
        } elseif (!$isUpdate && $date < new DateTimeImmutable('today')) {
            $errors['tanggal_kunjungan'] = 'Tanggal kunjungan tidak boleh sebelum hari ini.';
        }

        // --- Status (hanya saat ubah)
        $status = Status::tryFrom((string) ($input['status'] ?? Status::Menunggu->value));
        if ($isUpdate && $status === null) {
            $errors['status'] = 'Status tidak valid.';
        }

        $clean = [
            'nama'              => mb_convert_case($nama, MB_CASE_TITLE, 'UTF-8'),
            'umur'              => $umur === false ? null : $umur,
            'email'             => $email,
            'no_hp'             => $noHp,
            'pin'             => $pin,
            'jenis_kelamin'     => $jk?->value,   // ?-> (nullsafe operator): kalau $jk null, hasilnya null
            'gejala'            => $gejala,
            'poli_id'           => $poliId,
            'deskripsi'         => $deskripsi,
            'tanggal_kunjungan' => $tanggal,
            'status'            => $status?->value,
        ];

        return [$clean, $errors];
    }
}
