<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\UseCase\CreateOrder;

use App\Application\Exception\RequestValidationException;
use App\Application\UseCase\CreateOrder\CreateOrderRequest;
use App\Application\UseCase\CreateOrder\Request\CreateOrderDebtorCompanyRequest;
use App\Application\UseCase\CreateOrder\Request\CreateOrderDebtorPersonRequest;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\Tests\Helpers\FakeDataFiller;
use App\Tests\Integration\IntegrationTestCase;
use Ozean12\Money\TaxedMoney\TaxedMoneyFactory;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CreateOrderRequestTest extends IntegrationTestCase
{
    use ValidatedUseCaseTrait, FakeDataFiller;

    /** @test */
    public function shouldValidateRequestSuccessfully(): void
    {
        $createOrderRequest = $this->createValidRequest();

        $validator = $this->getContainer()->get(ValidatorInterface::class);
        $this->setValidator($validator);

        $this->expectNotToPerformAssertions();
        $this->validateRequest($createOrderRequest);
    }

    public function shouldHaveRequiredLineItemsForV2()
    {
        //TODO: Not sure if we want line items required on V2.
    }

    /**
     * @test
     */
    public function shouldFailWhenTheOrderAmountIsNotCorrect()
    {
        $request = $this->createValidRequest();
        $request->setAmount(TaxedMoneyFactory::create(200, 150, 9));

        $validator = $this->getContainer()->get(ValidatorInterface::class);
        $this->setValidator($validator);

        $this->expectException(RequestValidationException::class);
        $this->validateRequest($request);
    }

    /** @test */
    public function shouldFailWhenRequiredCompanyDataAreNotProvided()
    {
        $request = $this->createValidRequest();
        $request->setDebtor(new CreateOrderDebtorCompanyRequest());

        $validator = $this->getContainer()->get(ValidatorInterface::class);
        $this->setValidator($validator);

        $this->expectException(RequestValidationException::class);
        $this->validateRequest($request);
    }

    /** @test */
    public function shouldFailWhenEmailIsNotProvided()
    {
        $request = $this->createValidRequest();
        $request->getDebtorPerson()->setEmail(null);

        $validator = $this->getContainer()->get(ValidatorInterface::class);
        $this->setValidator($validator);

        $this->expectException(RequestValidationException::class);
        $this->validateRequest($request);
    }

    public function shouldFailWhenEmailIsNotValid()
    {
        $request = $this->createValidRequest();
        $request->getDebtorPerson()->setEmail('diar@asd.asd.');

        $validator = $this->getContainer()->get(ValidatorInterface::class);
        $this->setValidator($validator);

        $this->expectException(RequestValidationException::class);
        $this->validateRequest($request);
    }

    /** @test */
    public function shouldFailWhenCompanyAddressIsNotCorrect()
    {
        $request = $this->createValidRequest();
        $request->getDebtor()->setAddressStreet('some str.')
            ->setAddressCountry('GERMANY');

        $validator = $this->getContainer()->get(ValidatorInterface::class);
        $this->setValidator($validator);

        $this->expectException(RequestValidationException::class);
        $this->validateRequest($request);
    }

    private function createValidRequest(): CreateOrderRequest
    {
        $debtorCompany = new CreateOrderDebtorCompanyRequest();
        $this->fillObject($debtorCompany, true);
        $debtorCompany->setMerchantCustomerId('12')
            ->setName('$name')
            ->setLegalForm('1234');
        $debtorCompany->setAddressCountry('DE')
            ->setAddressPostalCode('10243')
            ->setAddressCity('Berlin');

        $debtorPerson = new CreateOrderDebtorPersonRequest();
        $debtorPerson->setEmail('diar@billie.io');

        $createOrderRequest = new CreateOrderRequest();

        $createOrderRequest
            ->setAmount(TaxedMoneyFactory::create(100, 90, 10))
            ->setDebtor($debtorCompany)
            ->setDuration(30)
            ->setMerchantId(1)
            ->setDebtorPerson($debtorPerson);

        return $createOrderRequest;
    }
}
