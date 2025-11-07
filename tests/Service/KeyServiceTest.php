<?php

declare(strict_types=1);

namespace Vigihdev\Encryption\Tests\Service;

use Vigihdev\Encryption\Service\KeyService;
use Vigihdev\Encryption\Tests\TestCase;

class KeyServiceTest extends TestCase
{
    private string $keyFilePath;
    private KeyService $keyService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->keyFilePath = sys_get_temp_dir() . '/defuse.key';
        $this->keyService = new KeyService($this->keyFilePath);
    }

    protected function tearDown(): void
    {
        if (file_exists($this->keyFilePath)) {
            unlink($this->keyFilePath);
        }
        parent::tearDown();
    }

    public function testGenerateKey(): void
    {
        $this->keyService->generateKey();
        $this->assertFileExists($this->keyFilePath);
    }

    public function testLoadKey(): void
    {
        $this->keyService->generateKey();
        $key = $this->keyService->loadKey();
        $this->assertNotEmpty($key);
    }
}
