<?php

declare(strict_types=1);

namespace App\Tests\Unit\Http\RequestTransformer;

use App\Application\UseCase\CheckoutConfirmOrder\CheckoutConfirmOrderRequest;
use App\Http\RequestTransformer\AmountRequestFactory;
use App\Http\RequestTransformer\CheckoutConfirmOrderRequestFactory;
use App\Http\RequestTransformer\CreateOrder\AddressRequestFactory;
use App\Support\StreetHouseParser;
use App\Tests\Unit\UnitTestCase;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Request;

class CheckoutConfirmOrderRequestFactoryTest extends UnitTestCase
{
    private string $requestV1Format = '{
  "amount": {
    "gross": 260.27,
    "net": 200.12,
    "tax": 60.15
  },
  "duration": 30,
  "debtor_company": {
    "name": "string",
    "address_addition": "string",
    "address_house_number": "string",
    "address_street": "string",
    "address_city": "string",
    "address_postal_code": "10969",
    "address_country": "DE"
  },
  "delivery_address": {
    "addition": "string",
    "house_number": "string",
    "street": "string",
    "city": "string",
    "postal_code": "10969",
    "country": "DE"
  },
  "order_id": "string"
}';

    private string $requestV2Format = '
{
  "amount": {
    "gross": 260.27,
    "net": 200.12,
    "tax": 60.15
  },
  "duration": 30,
  "debtor": {
    "name": "Billie",
    "company_address": {
        "house_number": "string",
        "street": "string",
        "postal_code": "10969",
        "city": "string",
        "country": "DE"
    }
  },
  "delivery_address": {
    "house_number": "string",
    "street": "string",
    "postal_code": "10969",
    "city": "string",
    "country": "DE"
  },
  "external_code": "string"
}
    ';

    /** @test */
    public function shouldCreateV2Request()
    {
        $request = new Request([], json_decode($this->requestV2Format, true), [], [], [], ['REQUEST_URI' => '/public/api/v2/checkout-confirm/order']);
        $factory = new CheckoutConfirmOrderRequestFactory(
            new AmountRequestFactory(),
            new AddressRequestFactory(new StreetHouseParser())
        );

        $useCaseRequest = $factory->create($request, Uuid::uuid4()->toString());
        self::assertTrue($useCaseRequest instanceof CheckoutConfirmOrderRequest);
        self::assertArrayHasKey('company_address', $useCaseRequest->getDebtorCompanyRequest()->toArray());
    }

    /** @test */
    public function shouldCreateV1Request()
    {
        $request = new Request([], json_decode($this->requestV1Format, true));
        $factory = new CheckoutConfirmOrderRequestFactory(
            new AmountRequestFactory(),
            new AddressRequestFactory(new StreetHouseParser())
        );

        $useCaseRequest = $factory->create($request, Uuid::uuid4()->toString());
        self::assertTrue($useCaseRequest instanceof CheckoutConfirmOrderRequest);
        self::assertArrayHasKey('address_postal_code', $useCaseRequest->getDebtorCompanyRequest()->toArray());
    }
}
