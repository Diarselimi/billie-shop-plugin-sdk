<?php

declare(strict_types=1);

namespace App\Helper\String;

class StringSearch
{
    private $germanCharactersProcessor;

    public function __construct(GermanCharactersProcessor $germanCharactersProcessor)
    {
        $this->germanCharactersProcessor = $germanCharactersProcessor;
    }

    public function cleanString(string $string): string
    {
        $string = preg_replace('/[\'\"`]/', ' ', $string);

        return trim(preg_replace('/\s+/', ' ', $string));
    }

    public function getGermanRegexpSearchKeyword(string $searchKeyword): string
    {
        return $this->getRegexpSearchKeyword(
            $this->germanCharactersProcessor->generateGermanCombinations($this->cleanString($searchKeyword))
        );
    }

    /**
     * @param  string[] $keywords
     * @return string
     */
    public function getRegexpSearchKeyword(array $keywords): string
    {
        return implode('|', $keywords);
    }
}
