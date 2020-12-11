<?php

declare(strict_types=1);

namespace App\Tests\Integration\Tests\Application\UseCase\CheckoutConfirmOrder;

use App\Application\Exception\RequestValidationException;
use App\Application\UseCase\CheckoutConfirmOrder\CheckoutConfirmOrderRequest;
use App\Application\UseCase\CreateOrder\Request\CreateOrderAddressRequest;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\DebtorCompany\DebtorCompanyRequest;
use App\Tests\Integration\Helpers\FakeDataFiller;
use App\Tests\Integration\Helpers\RandomDataTrait;
use App\Tests\Integration\IntegrationTestCase;
use Ozean12\Money\Money;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CheckoutConfirmOrderRequestTest extends IntegrationTestCase
{
    use ValidatedUseCaseTrait;
    use RandomDataTrait;
    use FakeDataFiller;

    /**
     * @test
     */
    public function validationShouldFailOnEmptyStringInExternalCode()
    {
        $request = new CheckoutConfirmOrderRequest();
        $request->setExternalCode('')
            ->setAmount(new TaxedMoney(new Money(3), new Money(2), new Money(1)))
            ->setDuration(30)
            ->setDebtorCompanyRequest(new DebtorCompanyRequest());

        $validator = $this->getContainer()->get(ValidatorInterface::class);
        $this->setValidator($validator);

        $this->expectException(RequestValidationException::class);
        $this->validateRequest($request);
    }

    /**
     * @test
     */
    public function validationShouldPassIfNoExternalCodeProvided()
    {
        $debtorCompanyRequest = new DebtorCompanyRequest();
        $this->fillObject($debtorCompanyRequest, true);
        $debtorCompanyRequest->setAddressRequest(
            (new CreateOrderAddressRequest())
                ->setPostalCode('12345')
                ->setCountry('DE')
                ->setHouseNumber('123')
                ->setStreet('test123')
                ->setCity('Stuttgart')
        );

        $request = new CheckoutConfirmOrderRequest();
        $request
            ->setAmount(new TaxedMoney(new Money(3), new Money(2), new Money(1)))
            ->setDuration(30)
            ->setDebtorCompanyRequest($debtorCompanyRequest);

        $validator = $this->getContainer()->get(ValidatorInterface::class);
        $this->setValidator($validator);
        $this->validateRequest($request);

        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function shouldPassWhenThereIsNoDurationProvided()
    {
        $debtorCompanyRequest = new DebtorCompanyRequest();
        $this->fillObject($debtorCompanyRequest, true);
        $debtorCompanyRequest
            ->setAddressRequest(
                (new CreateOrderAddressRequest())
                ->setPostalCode('12345')
                ->setCountry('DE')
                ->setHouseNumber('123')
                ->setStreet('test123')
                ->setCity('Stuttgart')
            );

        $request = new CheckoutConfirmOrderRequest();
        $request
            ->setAmount(new TaxedMoney(new Money(3), new Money(2), new Money(1)))
            ->setDebtorCompanyRequest($debtorCompanyRequest)
            ->setDuration(90);

        $validator = $this->getContainer()->get(ValidatorInterface::class);
        $this->setValidator($validator);
        $this->validateRequest($request);

        $this->assertTrue(true);
    }
}
