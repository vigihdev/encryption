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

        if (!is_file($keyFilepath)) {
            throw new InvalidArgumentException("File kunci enkripsi '{$keyFilepath}' tidak ditemukan atau bukan file.", 1);
        }

        $this->keyFilepath = $keyFilepath;
    }

    /**
     * getKey
     *
     * Memuat kunci dari file dan mengembalikannya sebagai objek Key.
     *
     * @return Key Objek kunci enkripsi.
     */
    public function getKey(): Key
    {
        return Key::loadFromAsciiSafeString(file_get_contents($this->keyFilepath));
    }
}
