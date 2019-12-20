<?php

declare(strict_types=1);

namespace App\DomainModel\BankAccount;

use App\Support\RandomStringGenerator;

class SepaMandateReferenceGenerator
{
    private const ALPHA_CHARS_LENGTH = 2;

    private const NUM_CHARS_LENGTH = 8;

    private const CHARSET_CAPITALS = 'ABCDEFGHIJKLMNPQRSTUVWXYZ';

    private const CHARSET_DIGITS = '123456789';

    private $randomStringGenerator;

    public function __construct(RandomStringGenerator $randomStringGenerator)
    {
        $this->randomStringGenerator = $randomStringGenerator;
    }

    public function generate(): string
    {
        return vsprintf(
            '%s%08d',
            [
                $this->randomStringGenerator->generateFromCharList(self::CHARSET_CAPITALS, self::ALPHA_CHARS_LENGTH),
                $this->randomStringGenerator->generateFromCharList(self::CHARSET_DIGITS, self::NUM_CHARS_LENGTH),
            ]
        );
    }
}
