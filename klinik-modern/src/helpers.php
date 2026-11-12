<?php

declare(strict_types=1);

/**
 * helpers.php — fungsi-fungsi kecil yang dipakai di mana-mana.
 * Semua fungsi pakai TYPE DECLARATION (string, int, array, ?string, never ...)
 * supaya kesalahan tipe data ketahuan lebih cepat (karena strict_types=1).
 */

// =====================================================================
// KEAMANAN OUTPUT
// =====================================================================

/** Escape output HTML -> cegah XSS. WAJIB dipakai setiap kali echo data dari user/database. */
function e(string|int|float|null $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// =====================================================================
// URL & REDIRECT
// =====================================================================

/** Membuat URL ke route.  url('pasien.edit', ['id' => 3]) -> "index.php?r=pasien.edit&id=3" */
function url(string $route, array $params = []): string
{
    return 'index.php?' . http_build_query(['r' => $route] + $params);
}

/**
 * Redirect lalu hentikan script.
 * Return type "never" (PHP 8.1) = fungsi ini dijamin tidak pernah kembali.
 */
function redirect(string $route, array $params = []): never
{
    header('Location: ' . url($route, $params), true, 303); // 303 See Other: setelah POST, browser pindah ke GET
    exit;
}

function abort(int $code = 404, string $message = 'Halaman tidak ditemukan'): never
{
    http_response_code($code);
    view('error', ['title' => "Error $code", 'code' => $code, 'message' => $message]);
    exit;
}

// =====================================================================
// VIEW (TEMPLATE)
// =====================================================================

/**
 * Render file di folder views/ ke dalam layout.
 * extract() mengubah ['title' => 'X'] menjadi variabel $title di dalam template.
 * ob_start() menampung output template dulu, lalu disisipkan ke layout sebagai $content.
 */
function view(string $template, array $data = []): void
{
    extract($data, EXTR_SKIP);

    ob_start();
    require BASE_PATH . "/views/{$template}.php";
    $content = ob_get_clean();

    require BASE_PATH . '/views/layout.php';
}

function config(string $key): mixed
{
    global $config;
    $value = $config;
    foreach (explode('.', $key) as $segment) {
        $value = $value[$segment] ?? null;
    }
    return $value;
}

// =====================================================================
// CSRF (Cross-Site Request Forgery) — materi "keamanan" Pertemuan 6
// Setiap form POST membawa token rahasia. Situs lain tidak tahu token ini,
// jadi tidak bisa memalsukan request atas nama user.
// =====================================================================

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['_token'] ?? '';
    // hash_equals = bandingkan string dengan waktu konstan (aman dari timing attack)
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        abort(419, 'Sesi formulir kedaluwarsa. Silakan muat ulang halaman dan coba lagi.');
    }
}

// =====================================================================
// FLASH MESSAGE — pesan sekali tampil setelah redirect (pengganti alert())
// =====================================================================

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

/** Ambil semua flash lalu hapus dari session (supaya tidak muncul lagi saat refresh). */
function pull_flashes(): array
{
    $flashes = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $flashes;
}

// =====================================================================
// FORMAT TAMPILAN
// =====================================================================

function rupiah(int|string|null $angka): string
{
    return 'Rp ' . number_format((int) $angka, 0, ',', '.');
}

function tanggal(string $tanggal, string $format = 'd-m-Y'): string
{
    return (new DateTimeImmutable($tanggal))->format($format);
}

function nama_hari(string $tanggal): string
{
    return match ((int) (new DateTimeImmutable($tanggal))->format('N')) {
        1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis',
        5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu',
    };
}

/** match(true) = cara ringkas pengganti if-elseif berantai */
function kategori_usia(int $umur): string
{
    return match (true) {
        $umur <= 12 => 'Anak-anak',
        $umur <= 17 => 'Remaja',
        $umur <= 59 => 'Dewasa',
        default     => 'Lansia',
    };
}
