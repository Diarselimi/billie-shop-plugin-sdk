<?php

namespace App\Helper\String;

class StringSearch
{
    public function searchWordsInString(array $needles, string $haystack): bool
    {
        foreach ($needles as $needle) {
            $result = mb_stripos($haystack, $needle);
            if ($result !== false) {
                return true;
            }
        }

        return false;
    }
}
