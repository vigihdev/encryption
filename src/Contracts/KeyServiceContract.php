<?php

declare(strict_types=1);

namespace Vigihdev\Encryption\Contracts;

use Defuse\Crypto\Key;

/**
 * KeyServiceContract
 *
 * Interface untuk service yang bertugas memuat dan menyediakan
 * kunci enkripsi (Defuse\Crypto\Key).
 */
interface KeyServiceContract
{
    /**
     * Mengembalikan objek kunci enkripsi.
     *
     * @return Key Objek Key dari Defuse.
     */
    public function getKey(): Key;
}
