<?php

declare(strict_types=1);

/**
 * Konfigurasi aplikasi di SATU tempat.
 * File ini me-return array, lalu dibaca di bootstrap.php:  $config = require 'config.php';
 * (Di framework seperti Laravel, ini mirip folder config/ + file .env)
 */
return [
    'app' => [
        'name'     => 'Klinik Sehat',
        'per_page' => 5,
        'timezone' => 'Asia/Jakarta',
    ],

    'db' => [
        'host'    => '127.0.0.1',
        'port'    => 3306,
        'name'    => 'klinik_modern',
        'user'    => 'root',
        'pass'    => '',
        'charset' => 'utf8mb4',
    ],

    'upload' => [
        'dir'      => __DIR__ . '/public/uploads', // lokasi fisik file
        'url'      => 'uploads',                   // path untuk <img src>
        'max_size' => 2 * 1024 * 1024,             // 2 MB
        // MIME asli (dibaca dari ISI file, bukan dari nama) => ekstensi yang dipakai saat simpan
        'mime'     => [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
        ],
    ],
];
