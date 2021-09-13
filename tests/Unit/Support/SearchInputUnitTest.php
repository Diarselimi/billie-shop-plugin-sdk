<?php

declare(strict_types=1);

namespace App\Tests\Unit\Support;

use App\Support\SearchInput;
use PHPUnit\Framework\TestCase;

class SearchInputUnitTest extends TestCase
{
    /**
     * @test
     */
    public function shouldConvertToUtf8(): void
    {
        $str = file_get_contents(__DIR__ . '/iso88992-string.txt');
        self::assertNotEquals('  hola cómo estás?  ', $str);

        $convertedStr = SearchInput::asString($str, false, 100);
        self::assertEquals('  hola cómo estás?  ', $convertedStr);

        $convertedStr = SearchInput::asString($str, true, 10);
        self::assertEquals('hola cómo', $convertedStr);
    }
}
