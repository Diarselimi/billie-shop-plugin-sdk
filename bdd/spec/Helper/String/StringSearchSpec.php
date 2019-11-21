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

        $this->isAnyWordsInString($words, $text)->shouldReturn(true);
    }

    public function it_fails_to_find_one_of_the_words_in_the_string()
    {
        $words = ['test', 'diar', 'new', 'random', 'not', 'another'];
        $text = 'A falsis, accentor fidelis agripeta.One moonlights studies most justices.Sunt gabaliumes consumere domesticus, audax stellaes.';

        $this->isAnyWordsInString($words, $text)->shouldReturn(false);
    }

    public function it_fail_to_find_no_words_in_string()
    {
        $this->isAnyWordsInString([], "some words here")->shouldReturn(false);
    }

    public function it_fails_to_find_words_in_empty_string()
    {
        $this->isAnyWordsInString(['test', 'another', 'new'], '')->shouldReturn(false);
    }

    public function it_fails_to_find_one_words_in_string()
    {
        $this->areAllWordsInString(['test', 'another', 'new'], 'test another diar test')->shouldReturn(false);
    }

    public function it_fails_to_find_any_words_in_empty_string()
    {
        $this->areAllWordsInString(['test', 'another', 'new'], '')->shouldReturn(false);
    }

    public function it_succeed_to_find_multiple_words_in_string()
    {
        $this->areAllWordsInString(['test', 'another', 'new'], 'test another new tasdqwe qasd qw e12e diar test')->shouldReturn(true);
    }

    public function it_succeed_to_find_multiple_words_with_special_chars_in_string()
    {
        $this->areAllWordsInString(['test', 'üml', 'neß'], 'test another neß üml tasdqwe qasd qw e12e diar test')->shouldReturn(true);
    }
}
