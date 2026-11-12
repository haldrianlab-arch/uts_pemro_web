<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\Gejala;

/**
 * Menghitung rincian biaya pasien (pola Latihan 4 P4: gaji pokok + tunjangan + lembur).
 *   Biaya pokok   = tarif poli
 *   Biaya gejala  = jumlah Gejala::biaya() untuk tiap gejala
 *   Diskon usia   = Anak-anak 20%, Lansia 30%
 *
 * Nilai ini TIDAK disimpan di database (derived value) supaya tidak pernah
 * "basi" ketika tarif poli atau gejala pasien diubah.
 */
final class BiayaCalculator
{
    /** @return array{biaya_pokok:int, biaya_gejala:int, subtotal:int, persen:int, diskon:int, total:int} */
    public static function hitung(int $tarifPoli, string $gejalaCsv, int $umur): array
    {
        $biayaGejala = array_sum(array_map(
            fn (string $g): int => Gejala::tryFrom($g)?->biaya() ?? 0,
            explode(',', $gejalaCsv),
        ));

        $subtotal = $tarifPoli + $biayaGejala;
        $persen   = match (kategori_usia($umur)) {
            'Anak-anak' => 20,
            'Lansia'    => 30,
            default     => 0,
        };
        $diskon = (int) round($subtotal * $persen / 100);

        return [
            'biaya_pokok'  => $tarifPoli,
            'biaya_gejala' => $biayaGejala,
            'subtotal'     => $subtotal,
            'persen'       => $persen,
            'diskon'       => $diskon,
            'total'        => $subtotal - $diskon,
        ];
    }

    /** Shortcut untuk 1 baris hasil query (butuh kolom tarif, gejala, umur). */
    public static function forRow(array $row): array
    {
        return self::hitung((int) $row['tarif'], (string) $row['gejala'], (int) $row['umur']);
    }
}
