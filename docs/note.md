```php
use Vigihdev\Encryption\Contracts\EnvironmentEncryptorServiceContract;
use VigihDev\SymfonyBridge\Config\AttributeInjection\DependencyInjector;
use VigihDev\SymfonyBridge\Config\AttributeInjection\Inject;
use VigihDev\SymfonyBridge\Config\ConfigBridge;
use VigihDev\SymfonyBridge\Config\Service\ServiceLocator;

ConfigBridge::boot( basePath: __DIR__, configDir: 'config', enableAutoInjection: true );

class TestDecrypt
{


    #[Inject(EnvironmentEncryptorServiceContract::class)]
    private EnvironmentEncryptorServiceContract $service;

    public function __construct()
    {
        DependencyInjector::inject($this);
    }

    public function test()
    {
        if ($this->service->isEncrypted(getenv('TEST_DB_DB_USER'))) {
            $dbUser = $this->service->decrypt(getenv('TEST_DB_DB_USER'));
            echo "DB USER DECRYPTED : " . $dbUser . "</br>";
        }
    }
}

$test = new TestDecrypt();
$test->test();

```
