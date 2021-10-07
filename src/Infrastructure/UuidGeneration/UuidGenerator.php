<?php

namespace App\Infrastructure\UuidGeneration;

interface UuidGenerator
{
    public function generate(): string;
}
