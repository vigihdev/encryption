<?php

declare(strict_types=1);

namespace Vigihdev\Encryption\Contracts;

/**
 * EnvironmentEncryptorServiceContract
 *
 * Interface untuk service yang menyediakan fungsionalitas
 * enkripsi dan dekripsi untuk environment.
 */
interface EnvironmentEncryptorServiceContract
{
    /**
     * Mendekripsi sebuah ciphertext menjadi plain text.
     *
     * @param string $ciphertext Teks terenkripsi.
     * @return string Teks asli hasil dekripsi.
     */
    public function decrypt(string $ciphertext): string;

    /**
     * Mengenkripsi variabel-variabel tertentu di dalam sebuah file .env.
     *
     * @param string $envPath Path absolut menuju file .env.
     * @param string[] $envKeys Array dari key environment yang akan dienkripsi.
     * @return bool `true` jika ada file yang dienkripsi, `false` jika tidak.
     */
    public function encryptEnvironment(string $envPath, array $envKeys): bool;

    /**
     * isEncrypted
     *
     * Memeriksa apakah sebuah value sudah dienkripsi.
     *
     * @param string $value
     * @return bool
     */
    public function isEncrypted(string $value): bool;
}
