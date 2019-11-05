<?php

namespace App\Support;

class TokenGenerator
{
    public function generate(int $length = 36, int $randomBytes = 32): string
    {
        return base_convert(bin2hex(random_bytes($randomBytes)), 16, $length);
    }
}
