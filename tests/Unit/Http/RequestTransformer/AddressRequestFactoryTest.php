<?php

declare(strict_types=1);

namespace App\Tests\Unit\Http\RequestTransformer;

use App\Http\RequestTransformer\CreateOrder\AddressRequestFactory;
use App\Support\StreetHouseParser;
use App\Tests\Unit\UnitTestCase;
use Prophecy\Argument;
use Psr\Log\NullLogger;

class AddressRequestFactoryTest extends UnitTestCase
{
    private $parser;

    private AddressRequestFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parser = $this->prophesize(StreetHouseParser::class);
        $this->factory = (new AddressRequestFactory($this->parser->reveal()))
            ->setLogger(new NullLogger());
    }

    /** @test */
    public function shouldUseStreetAndHouseFromRequest()
    {
        $request = [
            'street' => 'Charlottenstr.',
            'house_number' => '4a',
        ];

        $this->parser->extractStreetAndHouse(Argument::any())->shouldNotBeCalled();
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

        $this->parser->extractStreetAndHouse('Charlottenstr.')->shouldBeCalled()->willReturn(['Charlo', '']);
        $address = $this->factory->createFromArray($request);

        self::assertEquals('Charlo', $address->getStreet());
        self::assertEquals('', $address->getHouseNumber());
    }

    /** @test */
    public function shouldHaveStreetAndHouseFromParser()
    {
        $request = [
            'street' => 'Charlottenstr. 4a',
            'house_number' => '',
        ];

        $this->parser->extractStreetAndHouse('Charlottenstr. 4a')->shouldBeCalled()->willReturn(['Charlo', '4a']);
        $address = $this->factory->createFromArray($request);

        self::assertEquals('Charlo', $address->getStreet());
        self::assertEquals('4a', $address->getHouseNumber());
    }
}
