<?php

declare(strict_types=1);

namespace Vigihdev\Encryption\Tests\Service;

use Vigihdev\Encryption\Contracts\KeyServiceContract;
use Vigihdev\Encryption\Service\EnvironmentEncryptorService;
use Vigihdev\Encryption\Tests\TestCase;

class EnvironmentEncryptorServiceTest extends TestCase
{
    private string $envFilePath;
    private string $encryptedFilePath;
    private EnvironmentEncryptorService $encryptorService;

    protected function setUp(): void
    {
        parent::setUp();

        $keyService = $this->createMock(KeyServiceContract::class);
        $keyService->method('loadKey')->willReturn('def000006805357acc040535a9a4b5b7305b012a52016bd5b1b65b010f32a181012b0578f3e60532853c15121a182a2b353c1a3c3a3c3a3c3a3c3a3c3a3c3a3c3a3c3a3c');

        $this->encryptorService = new EnvironmentEncryptorService($keyService);

        $this->envFilePath = sys_get_temp_dir() . '/.env';
        $this->encryptedFilePath = sys_get_temp_dir() . '/.env.encrypted';

        file_put_contents($this->envFilePath, 'APP_SECRET=my_super_secret');
    }

    protected function tearDown(): void
    {
        if (file_exists($this->envFilePath)) {
            unlink($this->envFilePath);
        }
        if (file_exists($this->encryptedFilePath)) {
            unlink($this->encryptedFilePath);
        }
        parent::tearDown();
    }

    public function testEncrypt(): void
    {
        $this->encryptorService->encrypt($this->envFilePath, $this->encryptedFilePath);
        $this->assertFileExists($this->encryptedFilePath);
    }

    public function testDecrypt(): void
    {
        $this->encryptorService->encrypt($this->envFilePath, $this->encryptedFilePath);
        $decryptedFilePath = sys_get_temp_dir() . '/.env.decrypted';
        $this->encryptorService->decrypt($this->encryptedFilePath, $decryptedFilePath);
        $this->assertFileExists($decryptedFilePath);
        $this->assertStringEqualsFile($this->envFilePath, file_get_contents($decryptedFilePath));
    }
}
