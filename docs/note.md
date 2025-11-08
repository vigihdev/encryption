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

```bash
./bin/console env:beautiful .env
./bin/console env:decrypt .env SIRENT_SSH_HOST SIRENT_SSH_PORT SIRENT_SSH_USER SIRENT_SSH_REMOTE_PATH -o .env
./bin/console env:encrypt .env SIRENT_SSH_HOST SIRENT_SSH_PORT SIRENT_SSH_USER SIRENT_SSH_REMOTE_PATH

```

```
DEFUSE_KEY
SSH_ID_RSA_PATH

TEST_DB_DB_HOST
TEST_DB_DB_NAME
TEST_DB_DB_USER
TEST_DB_DB_PASSWORD

SIRENT_SSH_HOST SIRENT_SSH_PORT SIRENT_SSH_USER SIRENT_SSH_REMOTE_PATH
OKKARENT_ORG_SSH_HOST
OKKARENT_ORG_SSH_PORT
OKKARENT_ORG_SSH_USER
OKKARENT_ORG_SSH_REMOTE_PATH
SATIS_OKKARENT_SSH_HOST
SATIS_OKKARENT_SSH_PORT
SATIS_OKKARENT_SSH_USER
SATIS_OKKARENT_SSH_REMOTE_PATH
DOTRENTCAR_COM_OKKARENT_SSH_HOST
DOTRENTCAR_COM_OKKARENT_SSH_PORT
DOTRENTCAR_COM_OKKARENT_SSH_USER
DOTRENTCAR_COM_OKKARENT_SSH_REMOTE_PATH
THRUBUS_ID_OKKARENT_SSH_HOST
THRUBUS_ID_OKKARENT_SSH_PORT
THRUBUS_ID_OKKARENT_SSH_USER
THRUBUS_ID_OKKARENT_SSH_REMOTE_PATH
REPO_THRUBUS_ID_OKKARENT_SSH_HOST
REPO_THRUBUS_ID_OKKARENT_SSH_PORT
REPO_THRUBUS_ID_OKKARENT_SSH_USER
REPO_THRUBUS_ID_OKKARENT_SSH_REMOTE_PATH
OMAHTRANS_COM_OKKA_SSH_HOST
OMAHTRANS_COM_OKKA_SSH_PORT
OMAHTRANS_COM_OKKA_SSH_USER
OMAHTRANS_COM_OKKA_SSH_REMOTE_PATH
MECCARENTCAR_COM_OKKA_SSH_HOST
MECCARENTCAR_COM_OKKA_SSH_PORT
MECCARENTCAR_COM_OKKA_SSH_USER
MECCARENTCAR_COM_OKKA_SSH_REMOTE_PATH
```
