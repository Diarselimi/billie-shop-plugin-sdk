<?php

namespace App\Helper\String;

class StringSearch
{
    public function isAnyWordsInString(array $needles, string $haystack): bool
    {
        foreach ($needles as $needle) {
            $result = mb_stripos($haystack, $needle);
            if ($result !== false) {
                return true;
            }
        }

        return false;
    }

    public function areAllWordsInString(array $needles, string $haystack): bool
    {
        foreach ($needles as $needle) {
            if (mb_stripos($haystack, $needle) === false) {
                return false;
            }
        }

        return true;
    }
}
