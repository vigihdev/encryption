<?php

declare(strict_types=1);

namespace Vigihdev\Encryption\Handler;

use InvalidArgumentException;
use Vigihdev\Encryption\Contracts\EnvironmentBeautifulContract;
use Symfony\Component\Dotenv\Dotenv;

final class EnvironmentBeautifulHandler implements EnvironmentBeautifulContract
{

    public function __construct(
        private readonly string $envPath
    ) {
        if (!is_file($envPath)) {
            throw new InvalidArgumentException("File '{$envPath}' tidak ditemukan", 1);
        }
    }

    public function getEnvPath(): string
    {
        return $this->envPath;
    }

    public function toBeautifulEnvironment(): string
    {

        $results = [];
        foreach ($this->getEnvironments() as $prefix => $envs) {
            if (is_array($envs)) {
                $lines = [];
                foreach ($envs as $env) {
                    $lines[] = "{$env->getKey()}={$env->getValue()}";
                }

                if (count($envs) > 1) {
                    $lines = array_merge(['', "# {$prefix}"], $lines);
                }

                $results[] = implode(PHP_EOL, $lines);
            }
        }

        return implode(PHP_EOL, $results);
    }

    /**
     *
     * @return array<string,EnvironmentVariableHandler[]>
     */
    private function getEnvironments(): array
    {
        $results = [];
        $dotenvs = (new Dotenv())->parse(file_get_contents($this->envPath));
        foreach ($dotenvs as $key => $value) {
            preg_match('/^[a-z-A-Z]+/', $key, $matches);
            $prefix = is_string(current($matches)) ? current($matches) : null;
            $results[$prefix][] = new EnvironmentVariableHandler(
                prefix: $prefix,
                key: $key,
                value: $value,
            );
        }
        return $results;
    }
}
