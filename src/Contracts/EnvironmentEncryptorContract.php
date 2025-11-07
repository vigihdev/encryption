<?php

declare(strict_types=1);

namespace Vigihdev\Encryption\Contracts;

/**
 * EnvironmentEncryptorContract
 *
 * Interface untuk class yang bertugas melakukan enkripsi
 * variabel di dalam file .env.
 */
interface EnvironmentEncryptorContract
{
    /**
     * Mengembalikan status keberhasilan proses enkripsi.
     *
     * @return bool `true` jika berhasil, `false` jika gagal.
     */
    public function getSuccessEncrypt(): bool;

    /**
     * Mengembalikan array berisi key dan value yang berhasil dienkripsi.
     *
     * @return array<string, string>
     */
    public function getEnvEncrypts(): array;

    /**
     * Menjalankan proses enkripsi.
     *
     * @return void
     */
    public function encrypt(): void;
}
