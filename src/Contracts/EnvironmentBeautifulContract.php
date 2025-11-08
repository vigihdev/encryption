<?php

declare(strict_types=1);

namespace Vigihdev\Encryption\Contracts;


interface EnvironmentBeautifulContract
{
    public function getEnvPath(): string;

    public function toBeautifulEnvironment(): string;
}
