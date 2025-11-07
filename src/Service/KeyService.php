<?php

declare(strict_types=1);

namespace Vigihdev\Encryption\Service;

use Defuse\Crypto\Key;
use InvalidArgumentException;
use Vigihdev\Encryption\Contracts\KeyServiceContract;

/**
 * KeyService
 *
 * Class untuk mengelola dan memuat Defuse Crypto Key dari file.
 * Mendukung path home directory (misal: '~/keyfile').
 */
final class KeyService implements KeyServiceContract
{
    /**
     * @var string Path absolut menuju file kunci.
     */
    private string $keyFilepath;

    /**
     * @param string $keyFilepath Path menuju file kunci enkripsi.
     * @throws InvalidArgumentException Jika file kunci tidak ditemukan.
     */
    public function __construct(string $keyFilepath)
    {
        // Expand tilde (~) to home directory path
        if (str_starts_with($keyFilepath, '~/')) {
            $home = getenv('HOME') ?: ($_SERVER['HOME'] ?? null);
            if ($home) {
                $keyFilepath = $home . substr($keyFilepath, 1);
            }
        }

        $this->keyFilepath = $keyFilepath;
    }

    /**
     * loadKey
     *
     * Memuat kunci dari file dan mengembalikannya sebagai objek Key.
     *
     * @return Key Objek kunci enkripsi.
     */
    public function loadKey(): Key
    {
        if (!is_file($this->keyFilepath)) {
            throw new InvalidArgumentException("File kunci enkripsi '{$this->keyFilepath}' tidak ditemukan atau bukan file.", 1);
        }
        return Key::loadFromAsciiSafeString(file_get_contents($this->keyFilepath));
    }

    /**
     * generateKey
     *
     * Membuat kunci enkripsi baru dan menyimpannya ke file.
     *
     * @return void
     */
    public function generateKey(): void
    {
        $key = Key::createNewRandomKey();
        file_put_contents($this->keyFilepath, $key->saveToAsciiSafeString());
    }
}
