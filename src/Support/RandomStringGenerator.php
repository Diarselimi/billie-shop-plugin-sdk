<?php

declare(strict_types=1);

namespace App\Support;

class RandomStringGenerator
{
    public function generateHexToken(int $length = 32): string
    {
        return $this->generateToken($length, 16);
    }

    private function generateToken(int $length = 32, int $base = 16): string
    {
        $bytes = bin2hex(random_bytes($length));
        if ($base != 16) {
            $bytes = base_convert($bytes, 16, min(36, $base));
        }

        return substr($bytes, 0, $length);
    }

    public function generateFromCharList(string $charList, int $length = 32): string
    {
        $charListLastIndex = strlen($charList) - 1;
        $result = '';

        for ($i = 0; $i < $length; $i++) {
            $randChar = $charList[random_int(0, $charListLastIndex)];
            $result .= $randChar;
        }

        return $result;
    }
}
