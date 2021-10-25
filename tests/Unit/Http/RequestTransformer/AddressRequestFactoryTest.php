<?php

declare(strict_types=1);

namespace App\Tests\Unit\Http\RequestTransformer;

use App\Http\RequestTransformer\CreateOrder\AddressRequestFactory;
use App\Tests\Unit\UnitTestCase;
use Psr\Log\NullLogger;

class AddressRequestFactoryTest extends UnitTestCase
{
    private AddressRequestFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = (new AddressRequestFactory())
            ->setLogger(new NullLogger());
    }

    /** @test */
    public function shouldUseStreetAndHouseFromRequest()
    {
        $request = [
            'street' => 'Charlottenstr.',
            'house_number' => '4a',
        ];

        $address = $this->factory->createFromArray($request);

        self::assertEquals('Charlottenstr.', $address->getStreet());
        self::assertEquals('4a', $address->getHouseNumber());
    }

    /** @test */
    public function shouldUseStreetAndHouseFromRequestIfParsingFailed()
    {
        $request = [
            'street' => 'Charlottenstr.',
            'house_number' => '',
        ];

        $address = $this->factory->createFromArray($request);

        self::assertEquals('Charlottenstr.', $address->getStreet());
        self::assertEquals('', $address->getHouseNumber());
    }

    /** @test */
    public function shouldHaveStreetAndHouseFromParser()
    {
        $request = [
            'street' => 'Charlottenstr. 4a',
            'house_number' => '',
        ];

        $address = $this->factory->createFromArray($request);

        self::assertEquals('Charlottenstr.', $address->getStreet());
        self::assertEquals('4a', $address->getHouseNumber());
    }

    /** @test */
    public function shouldHaveStreetAndHouseFromParserLegacyRequest()
    {
        $request = [
            'address_street' => 'Charlottenstr. 4a',
            'address_house_number' => '',
        ];

        $address = $this->factory->createFromOldFormat($request);

        self::assertEquals('Charlottenstr.', $address->getStreet());
        self::assertEquals('4a', $address->getHouseNumber());
    }
}
