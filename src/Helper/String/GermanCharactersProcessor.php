<?php

declare(strict_types=1);

namespace App\Helper\String;

class GermanCharactersProcessor
{
    private const GERMAN_CHARS = [
        'ä' => 'ae',
        'Ä' => 'Ae',
        'ö' => 'oe',
        'Ö' => 'Oe',
        'ü' => 'ue',
        'Ü' => 'Ue',
        'ß' => 'ss',
    ];

    /**
     * @param  string   $string
     * @param  string[] $combinations
     * @return string[]
     */
    public function generateGermanCombinations(string $string, array &$combinations = []): array
    {
        if (empty($string)) {
            return $combinations;
        }
        if (empty($combinations)) {
            $combinations[] = $string;
        }

        foreach (self::GERMAN_CHARS as $char => $notation) {
            $position = 0;
            while (($position = strpos($string, $char, $position)) !== false) {
                $newWord = substr_replace($string, $notation, $position, 2);
                $position = $position + strlen($char);
                if (!in_array($newWord, $combinations)) {
                    $combinations[] = $newWord;
                    $this->generateGermanCombinations($newWord, $combinations);
                }
            }
        }

        return $combinations;
    }
}
