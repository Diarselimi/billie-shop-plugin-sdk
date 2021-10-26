<?php

declare(strict_types=1);

namespace App\Tests\Domain\Merchant;

use App\DomainModel\Merchant\PartnerIdentifier;
use App\Tests\Unit\UnitTestCase;

class MerchantIdentifierTest extends UnitTestCase
{
    /** @test */
    public function shouldCheckIfMerchantIdentifierIsPrefixed(): void
    {
        $merchantIdentifier = PartnerIdentifier::create('external_code');
        self::assertEquals((string) $merchantIdentifier, 'external_code');
    }
}
