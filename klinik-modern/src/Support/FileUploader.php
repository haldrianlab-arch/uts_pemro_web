<?php

declare(strict_types=1);

namespace App\Support;

use finfo;
use RuntimeException;

/**
 * Upload file yang lebih aman dibanding versi dasar:
 *  1. Tipe file dicek dari ISI file (finfo), bukan dari $_FILES['type']
 *     -> $_FILES['type'] dikirim oleh browser, jadi BISA DIPALSUKAN.
 *  2. Nama file baru dibuat acak total (random_bytes) + ekstensi dari MIME asli
 *     -> file "virus.php" yang menyamar jadi gambar tidak akan tersimpan sebagai .php
 *  3. is_uploaded_file() memastikan file benar-benar hasil upload HTTP.
 */
final class FileUploader
{
    /** @param array<string,string> $allowedMime  ['image/jpeg' => 'jpg', ...] */
    public function __construct(
        private readonly string $dir,
        private readonly int $maxSize,
        private readonly array $allowedMime,
    ) {
    }

    /** Ada file yang dipilih user? */
    public static function hasFile(?array $file): bool
    {
        return $file !== null && ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE;
    }

    /** @return string|null pesan error, atau null kalau valid */
    public function validate(?array $file, bool $required): ?string
    {
        if (!self::hasFile($file)) {
            return $required ? 'Bukti bayar wajib diunggah.' : null;
        }

        return match (true) {
            $file['error'] === UPLOAD_ERR_INI_SIZE,
            $file['error'] === UPLOAD_ERR_FORM_SIZE  => 'Ukuran file terlalu besar.',
            $file['error'] !== UPLOAD_ERR_OK         => 'Gagal mengunggah file (kode ' . $file['error'] . ').',
            !is_uploaded_file($file['tmp_name'])     => 'File tidak valid.',
            $file['size'] > $this->maxSize           => 'Ukuran file maksimum ' . ($this->maxSize / 1024 / 1024) . ' MB.',
            $this->detectExtension($file) === null   => 'Hanya file JPG dan PNG yang diizinkan.',
            default                                  => null,
        };
    }

    /** Simpan file, return nama file baru. Panggil validate() dulu sebelum ini. */
    public function store(array $file): string
    {
        $ext  = $this->detectExtension($file) ?? throw new RuntimeException('Tipe file tidak diizinkan.');
        $name = bin2hex(random_bytes(16)) . '.' . $ext;

        if (!move_uploaded_file($file['tmp_name'], $this->dir . '/' . $name)) {
            throw new RuntimeException('Gagal menyimpan file.');
        }
        return $name;
    }

    public function delete(?string $name): void
    {
        // basename() mencegah path traversal, mis. "../../config.php"
        $path = $this->dir . '/' . basename((string) $name);
        if ($name && is_file($path)) {
            unlink($path);
        }
    }

    private function detectExtension(array $file): ?string
    {
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        return $this->allowedMime[$mime] ?? null;
    }
}
