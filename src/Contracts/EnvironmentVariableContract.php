<?php

declare(strict_types=1);

namespace Vigihdev\Encryption\Contracts;

interface EnvironmentVariableContract
{
    public function getPrefix(): string;
    public function getKey(): string;
    public function getValue(): string;
}
