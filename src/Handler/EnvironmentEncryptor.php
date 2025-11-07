<?php

declare(strict_types=1);

namespace Vigihdev\Encryption\Handler;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use InvalidArgumentException;
use Symfony\Component\Dotenv\Dotenv;
use Vigihdev\Encryption\Contracts\EnvironmentEncryptorContract;

/**
 * EnvironmentEncryptor
 *
 * Class untuk mengenkripsi variabel dalam file .env
 */
final class EnvironmentEncryptor implements EnvironmentEncryptorContract
{
    /**
     * @var string Prefix untuk menandai value yang sudah dienkripsi.
     */
    private const ENCRYPTED_PREFIX = 'def50200';

    /**
     * @var bool Status keberhasilan proses enkripsi.
     */
    private bool $successEncrypt = false;

    /**
     * @var array<string, string> Variabel environment yang berhasil dienkripsi.
     */
    private array $envEncrypts = [];

    /**
     * @var array<string, string> Variabel environment yang sudah di-parse.
     */
    private array $parsedEnvs = [];

    /**
     * @param Key $key Kunci enkripsi.
     * @param string $envPath Path menuju file .env.
     * @param string[] $envKeys Key dari environment yang akan dienkripsi.
     * @throws InvalidArgumentException Jika file .env tidak ditemukan.
     */
    public function __construct(
        private readonly Key $key,
        private readonly string $envPath,
        private readonly array $envKeys
    ) {
        if (!is_file($envPath)) {
            throw new InvalidArgumentException("File Env {$envPath} tidak tersedia.", 1);
        }
        $this->parsedEnvs = $this->parseEnvs();
    }

    /**
     * getSuccessEncrypt
     *
     * Mengembalikan status keberhasilan enkripsi.
     *
     * @return bool
     */
    public function getSuccessEncrypt(): bool
    {
        return $this->successEncrypt;
    }

    /**
     * getEnvEncrypts
     *
     * Mengembalikan array dari environment yang dienkripsi.
     *
     * @return array<string, string>
     */
    public function getEnvEncrypts(): array
    {
        return $this->envEncrypts;
    }

    /**
     * parseEnvs
     *
     * Membaca dan mem-parsing file .env.
     *
     * @return array<string, string>
     */
    private function parseEnvs(): array
    {
        return (new Dotenv())->parse(file_get_contents($this->envPath));
    }

    /**
     * encrypt
     *
     * Menjalankan proses enkripsi pada key yang ditentukan.
     */
    public function encrypt(): void
    {
        $envToUpdate = [];
        foreach ($this->envKeys as $envKey) {
            $value = $this->parsedEnvs[$envKey] ?? null;

            if (is_string($value) && !$this->isEncrypted($value)) {
                $encryptedValue = Crypto::encrypt($value, $this->key);
                $envToUpdate[$envKey] = $encryptedValue;
                $this->envEncrypts[$envKey] = $encryptedValue;
            }
        }

        if (empty($envToUpdate)) {
            return;
        }

        // Gabungkan env yang diupdate dengan yang sudah ada
        $newEnvs = array_merge($this->parsedEnvs, $envToUpdate);
        $this->successEncrypt = $this->saveEnv($newEnvs);
    }

    /**
     * saveEnv
     *
     * Menyimpan data environment ke dalam file .env.
     *
     * @param array<string, string> $data Data untuk disimpan.
     * @return bool
     */
    private function saveEnv(array $data): bool
    {
        $envData = array_map(
            fn($value, $key) => "{$key}=$value",
            $data,
            array_keys($data)
        );

        $envData = implode(PHP_EOL, $envData);
        return (bool) file_put_contents($this->envPath, $envData);
    }

    /**
     * isEncrypted
     *
     * Memeriksa apakah sebuah value sudah dienkripsi.
     *
     * @param string $value
     * @return bool
     */
    private function isEncrypted(string $value): bool
    {
        return str_starts_with($value, self::ENCRYPTED_PREFIX);
    }
}
