<?php

declare(strict_types=1);

namespace App\Tests\Unit\Support;

use App\Support\TwoWayEncryption\OpenSslEcbEncryption;
use App\Tests\Unit\UnitTestCase;

class EncryptDecryptTest extends UnitTestCase
{
    /** @test */
    public function itShouldDecryptThePhraseToThePlainText()
    {
        $encryptDecrypt = new OpenSslEcbEncryption('key');

        self::assertNotEquals('some_word', $encryptDecrypt->encrypt('some_word'));
    }

    /**
     * @test
     * @dataProvider dataProvider
     */
    public function itShouldEncryptThePlainTextToMatchTheKey($word)
    {
        $encryptDecrypt = new OpenSslEcbEncryption('key');

        self::assertEquals($word, $encryptDecrypt->decrypt($encryptDecrypt->encrypt($word)));
    }

    /** @test */
    public function itShouldNotEncryptEmptyStringOrNull()
    {
        $encryptDecrypt = new OpenSslEcbEncryption('key');

        self::assertEquals('', $encryptDecrypt->encrypt(''));
    }

    public function dataProvider(): array
    {
        return [
            ['!$%@$#^%YRTEGDF'],
            ['asdfagQWEDASFGFD'],
            ['QWERR#@$%%#@%YRTHTEWTGFDERWTGFDHTEWRTGDHFDSTFGHDGTFGDHFDTRFDGHGSFSFDHGFHGF'],
            ['QWE'],
            [''],
            ['1234567890'],
            ['Länder und NationalitätenLänder und Nationalitäten'],
            ['/QWEASD?QW+QAD?QWRDFSAF+ASDWAF:?QAF?////////'],
            ['------------123------------------'],
            ['0000000000000000000000000000000000'],
            [';;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;;'],
        ];
    }
}
