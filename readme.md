```yaml
# security.yaml
services:
  Vigihdev\Encryption\Contracts\KeyServiceContract:
    class: 'Vigihdev\Encryption\Service\KeyService'
    arguments:
      $keyFilepath: "%env(DEFUSE_KEY)%"

  Vigihdev\Encryption\Contracts\EnvironmentEncryptorServiceContract:
    public: true
    class: 'Vigihdev\Encryption\Service\EnvironmentEncryptorService'
    arguments:
      $keyService: '@Vigihdev\Encryption\Contracts\KeyServiceContract'
```
