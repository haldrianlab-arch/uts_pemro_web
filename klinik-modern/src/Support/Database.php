<?php

declare(strict_types=1);

namespace App\Support;

use PDO;

/**
 * Membuat koneksi PDO sekali saja per request.
 *
 * Kenapa PDO, bukan mysqli?
 *  - Satu API untuk banyak database (MySQL, PostgreSQL, SQLite, ...)
 *  - Prepared statement dengan named parameter (:nama) -> kebal SQL Injection
 *  - Error otomatis jadi Exception -> tidak ada error yang "diam-diam" terlewat
 */
final class Database
{
    public static function connect(array $db): PDO
    {
        $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset={$db['charset']}";

        return new PDO($dsn, $db['user'], $db['pass'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // error -> lempar PDOException
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // hasil fetch = associative array
            PDO::ATTR_EMULATE_PREPARES   => false,                  // pakai prepared statement asli MySQL
        ]);
    }
}
