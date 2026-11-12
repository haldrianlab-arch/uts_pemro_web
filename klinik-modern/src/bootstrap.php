<?php

declare(strict_types=1);

/**
 * bootstrap.php — dijalankan PERTAMA KALI oleh public/index.php di setiap request.
 * Tugasnya "menyiapkan panggung":
 *   1. Baca konfigurasi
 *   2. Daftarkan autoloader (supaya tidak perlu require satu-satu file class)
 *   3. Mulai session (untuk flash message & CSRF token)
 *   4. Load fungsi-fungsi helper
 */

const BASE_PATH = __DIR__ . '/..';

// 1. Konfigurasi
$config = require BASE_PATH . '/config.php';
date_default_timezone_set($config['app']['timezone']);

// 2. Autoloader sederhana ala PSR-4 (tanpa Composer)
//    Class  App\Repositories\PasienRepository
//    -> file src/Repositories/PasienRepository.php
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = str_replace('\\', '/', substr($class, strlen($prefix)));
    $file     = __DIR__ . '/' . $relative . '.php';
    if (is_file($file)) {
        require $file;
    }
});

// 3. Session dengan cookie yang lebih aman
session_start([
    'cookie_httponly' => true,   // cookie tidak bisa dibaca JavaScript (mengurangi dampak XSS)
    'cookie_samesite' => 'Lax',  // cookie tidak dikirim dari form situs lain (mengurangi CSRF)
    'use_strict_mode' => true,
]);

// 4. Helper global: e(), url(), redirect(), view(), csrf_field(), flash(), dll.
require __DIR__ . '/helpers.php';
