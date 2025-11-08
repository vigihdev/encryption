<?php

declare(strict_types=1);

namespace Vigihdev\Encryption\Handler;

use Vigihdev\Encryption\Contracts\EnvironmentVariableContract;

final class EnvironmentVariableHandler implements EnvironmentVariableContract
{
    public function __construct(
        private readonly string $prefix,
        private readonly string $key,
        private readonly string $value,
    ) {}

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getValue(): string
    {
        return $this->value;
    }
}
