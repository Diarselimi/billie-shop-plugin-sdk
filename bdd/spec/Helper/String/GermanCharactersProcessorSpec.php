<?php

declare(strict_types=1);

namespace spec\App\Helper\String;

use App\Helper\String\GermanCharactersProcessor;
use PhpSpec\ObjectBehavior;

class GermanCharactersProcessorSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(GermanCharactersProcessor::class);
    }

    public function it_finds_all_umlauts()
    {
        $word = 'Grüße';

        $this->generateGermanCombinations($word)->shouldReturn(['Grüße', 'Grueße', 'Gruesse', 'Grüsse']);
    }

    public function it_does_not_find_umlauts()
    {
        $word = 'Gruesse';

        $this->generateGermanCombinations($word)->shouldReturn(['Gruesse']);
    }

    public function it_returns_empty_array_if_empty_string()
    {
        $word = '';

        $this->generateGermanCombinations($word)->shouldReturn([]);
    }
}
