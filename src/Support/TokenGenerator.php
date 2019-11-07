<?php

namespace App\Support;

class TokenGenerator
{
    public function generate(int $length = 32, int $base = 16): string
    {
        $bytes = bin2hex(random_bytes($length));
        if ($base != 16) {
            $bytes = base_convert($bytes, 16, min(36, $base));
        }

        return substr($bytes, 0, $length);
    }
}
