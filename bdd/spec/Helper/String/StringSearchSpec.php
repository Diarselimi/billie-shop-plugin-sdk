<?php

namespace spec\App\Helper\String;

use App\Helper\String\StringSearch;
use PhpSpec\ObjectBehavior;

class StringSearchSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(StringSearch::class);
    }

    public function it_finds_one_of_the_words_in_the_string()
    {
        $words = ['test', 'diar', 'new', 'random', 'not', 'another', 'most'];
        $text = 'A falsis, accentor fidelis agripeta.One moonlights studies most justices.Sunt gabaliumes consumere domesticus, audax stellaes.';

        $this->searchWordsInString($words, $text)->shouldReturn(true);
    }

    public function it_fails_to_find_one_of_the_words_in_the_string()
    {
        $words = ['test', 'diar', 'new', 'random', 'not', 'another'];
        $text = 'A falsis, accentor fidelis agripeta.One moonlights studies most justices.Sunt gabaliumes consumere domesticus, audax stellaes.';

        $this->searchWordsInString($words, $text)->shouldReturn(false);
    }

    public function it_fail_to_find_no_words_in_string()
    {
        $this->searchWordsInString([], "some words here")->shouldReturn(false);
    }

    public function it_fails_to_find_words_in_empty_string()
    {
        $this->searchWordsInString(['test', 'another', 'new'], '')->shouldReturn(false);
    }
}
