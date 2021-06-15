<?php

namespace App\Support;

class StreetHouseParser
{
    private const REGEX = '/^([a-zäöüß\s\d.,-]+?)\s*([\d\s]+(?:\s?[-|+\/]\s?\d+)?\s*[a-z]?)?$/iu';

    public function extractStreetAndHouse(string $input): array
    {
        $street = $input;
        $houseNumber = null;

        if (preg_match(self::REGEX, $input, $matches)) {
            $street = trim($matches[1]);
            $houseNumber = trim($matches[2] ?? '');
        }

        return [$street, $houseNumber];
    }
}
