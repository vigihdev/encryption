<?php

declare(strict_types=1);

namespace Vigihdev\Encryption\Service;

use Defuse\Crypto\Crypto;
use InvalidArgumentException;
use RuntimeException;
use Vigihdev\Encryption\Contracts\{EnvironmentEncryptorServiceContract, KeyServiceContract};
use Vigihdev\Encryption\Handler\EnvironmentEncryptor;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;

/**
 * EnvironmentEncryptorService
 *
 * Service untuk mengelola enkripsi dan dekripsi environment.
 */
final class EnvironmentEncryptorService implements EnvironmentEncryptorServiceContract
{
    /**
     * @var string Prefix untuk menandai value yang sudah dienkripsi.
     */
    private const ENCRYPTED_PREFIX = 'def50200';

    /**
     * @param KeyServiceContract $keyService Service untuk mendapatkan kunci enkripsi.
     */
    public function __construct(
        private readonly KeyServiceContract $keyService
    ) {}

    /**
     * decrypt
     *
     * Mendekripsi sebuah ciphertext.
     *
     * @param string $ciphertext Teks yang akan didekripsi.
     * @return string Teks hasil dekripsi.
     * @throws InvalidArgumentException Jika ciphertext tidak valid.
     * @throws RuntimeException Jika proses dekripsi gagal.
     */
    public function decrypt(string $ciphertext): string
    {
        if (!$this->isEncrypted($ciphertext)) {
            throw new InvalidArgumentException("Nilai '{$ciphertext}' bukan merupakan hasil enkripsi yang valid.");
        }

        try {
            return Crypto::decrypt($ciphertext, $this->keyService->getKey());
        } catch (WrongKeyOrModifiedCiphertextException $e) {
            throw new RuntimeException("Gagal mendekripsi '{$ciphertext}'. Pastikan kunci enkripsi benar.", 0, $e);
        }
    }

    /**
     * encryptEnvironment
     *
     * Mengenkripsi variabel dalam file .env.
     *
     * @param string $envPath Path menuju file .env.
     * @param string[] $envKeys List key yang akan dienkripsi.
     * @return bool Status keberhasilan.
     */
    public function encryptEnvironment(string $envPath, array $envKeys): bool
    {
        $encryptor = new EnvironmentEncryptor(
            key: $this->keyService->getKey(),
            envPath: $envPath,
            envKeys: $envKeys
        );
        $encryptor->encrypt();
        return $encryptor->getSuccessEncrypt();
    }

    /**
     * isEncrypted
     *
     * Memeriksa apakah sebuah value sudah dienkripsi.
     *
     * @param string $value
     * @return bool
     */
    public function isEncrypted(string $value): bool
    {
        return str_starts_with($value, self::ENCRYPTED_PREFIX);
    }
}
