<?php

declare(strict_types=1);

namespace App\Tests\Unit\Support;

use App\Support\StreetHouseParser;
use App\Tests\Unit\UnitTestCase;

class StreetHouseParserTest extends UnitTestCase
{
    /**
     * @test
     * @dataProvider dataProvider
     */
    public function shouldParseStreetAndHouseFromInput(string $input, string $expectedStreet, string $expectedHouse)
    {
        [$street, $house] = StreetHouseParser::extractStreetAndHouse($input);

        self::assertEquals($expectedStreet, $street);
        self::assertEquals($expectedHouse, $house);
    }

    public function dataProvider(): array
    {
        return [
            ['Gewerbegebiet Ziesegrund 17', 'Gewerbegebiet Ziesegrund', '17'],
            ['Sonnenwiechser Str.    42 1/2 ', 'Sonnenwiechser Str.', '42 1/2'],
            ['     Am    Hamberg 4/1    ', 'Am    Hamberg', '4/1'],
            ['Brüggerfeld 14 + 20', 'Brüggerfeld', '14 + 20'],
            ['Straße des 17. Juni 18+19', 'Straße des 17. Juni', '18+19'],
            ['Hochstr. 19', 'Hochstr.', '19'],
            ['Straße des 17. Juni 23-25 a', 'Straße des 17. Juni', '23-25 a'],
            ['Domerschulstrasse 234', 'Domerschulstrasse', '234'],
            ['Siemensplatz', 'Siemensplatz', ''],
            ['Kantstr. 112, Hinterhaus Ausgang B4', 'Kantstr. 112, Hinterhaus Ausgang B', '4'], // known issue
            ['Werinherstr 79, Gbd. 32 a', 'Werinherstr 79, Gbd.', '32 a'],
        ];
    }
}
