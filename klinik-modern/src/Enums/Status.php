<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * ENUM (PHP 8.1) = daftar nilai tetap yang "resmi".
 * Lebih aman daripada string biasa: salah ketik "Selsai" langsung ketahuan.
 * Nilainya ('Menunggu', dst) sama persis dengan ENUM di database.
 */
enum Status: string
{
    case Menunggu  = 'Menunggu';
    case Diperiksa = 'Diperiksa';
    case Selesai   = 'Selesai';

    /** Class CSS untuk badge. match() pengganti switch: lebih ringkas & wajib lengkap. */
    public function badgeClass(): string
    {
        return match ($this) {
            self::Menunggu  => 'badge badge--warning',
            self::Diperiksa => 'badge badge--info',
            self::Selesai   => 'badge badge--success',
        };
    }
}
