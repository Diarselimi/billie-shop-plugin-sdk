<?php

namespace spec\App\Helper\String;

use App\Helper\String\GermanCharactersProcessor;
use App\Helper\String\StringSearch;
use PhpSpec\ObjectBehavior;

class StringSearchSpec extends ObjectBehavior
{
    public function let(GermanCharactersProcessor $germanCharactersProcessor)
    {
        $this->beConstructedWith($germanCharactersProcessor);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(StringSearch::class);
    }

    public function it_generates_german_search_keyword(
        GermanCharactersProcessor $germanCharactersProcessor
    ) {
        $word = 'Grüße';

        $germanCharactersProcessor
            ->generateGermanCombinations($word)
            ->willReturn(['Grüße', 'Grueße', 'Gruesse', 'Grüsse']);

        $this->getGermanRegexpSearchKeyword($word)->shouldReturn('Grüße|Grueße|Gruesse|Grüsse');
    }

    public function it_generates_empty_search_keyword(
        GermanCharactersProcessor $germanCharactersProcessor
    ) {
        $word = '';

        $germanCharactersProcessor
            ->generateGermanCombinations($word)
            ->willReturn([]);

        $this->getGermanRegexpSearchKeyword($word)->shouldReturn('');
    }
}
