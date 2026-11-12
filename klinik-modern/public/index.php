<?php

declare(strict_types=1);

/**
 * FRONT CONTROLLER — satu-satunya file PHP yang dibuka browser.
 * Semua halaman lewat sini:  index.php?r=pasien.index, index.php?r=pasien.edit&id=3, dst.
 *
 * Alur setiap request:
 *   browser -> public/index.php -> bootstrap -> ROUTER (match) -> Controller -> Repository (DB)
 *                                                                   |
 *                                                                   v
 *                                                       view() -> HTML -> browser
 *
 * Kenapa folder public/ dipisah?
 *   Hanya isi public/ (index.php, css, uploads) yang boleh diakses dari browser.
 *   File config.php, src/, views/ berada DI LUAR public/ sehingga tidak bisa dibuka langsung.
 */

use App\Controllers\PasienController;
use App\Controllers\PoliController;
use App\Repositories\PasienRepository;
use App\Repositories\PoliRepository;
use App\Support\Database;
use App\Support\FileUploader;

require __DIR__ . '/../src/bootstrap.php';

// ---------------------------------------------------------------------
// 1. Rakit dependency (Dependency Injection manual)
//    Objek dibuat di sini lalu "disuntikkan" lewat constructor.
// ---------------------------------------------------------------------
try {
    $pdo = Database::connect($config['db']);
} catch (PDOException $e) {
    abort(500, 'Tidak bisa terhubung ke database. Pastikan MySQL di XAMPP menyala dan database.sql sudah di-import. (' . $e->getMessage() . ')');
}

$poliRepo   = new PoliRepository($pdo);
$pasienRepo = new PasienRepository($pdo);
$uploader   = new FileUploader($config['upload']['dir'], $config['upload']['max_size'], $config['upload']['mime']);

$pasien = new PasienController($pasienRepo, $poliRepo, $uploader, $config['app']['per_page']);
$poli   = new PoliController($poliRepo);

// ---------------------------------------------------------------------
// 2. Routing
// ---------------------------------------------------------------------
$method = $_SERVER['REQUEST_METHOD'];
$route  = (string) ($_GET['r'] ?? 'pasien.index');
$id     = (int) ($_GET['id'] ?? 0);

// Semua request POST WAJIB membawa token CSRF yang benar
if ($method === 'POST') {
    verify_csrf();
}

// match() membandingkan [METHOD, ROUTE] dengan ===  (array harus sama persis)
// Hapus/ubah hanya menerima POST -> link GET biasa tidak bisa menghapus data.
match ([$method, $route]) {
    ['GET',  'pasien.index']   => $pasien->index(),
    ['GET',  'pasien.show']    => $pasien->show($id),
    ['GET',  'pasien.create']  => $pasien->create(),
    ['POST', 'pasien.store']   => $pasien->store(),
    ['GET',  'pasien.edit']    => $pasien->edit($id),
    ['POST', 'pasien.update']  => $pasien->update($id),
    ['POST', 'pasien.destroy'] => $pasien->destroy($id),

    ['GET',  'poli.index']     => $poli->index(),
    ['POST', 'poli.store']     => $poli->store(),
    ['POST', 'poli.update']    => $poli->update($id),
    ['POST', 'poli.destroy']   => $poli->destroy($id),

    default                    => abort(404),
};
