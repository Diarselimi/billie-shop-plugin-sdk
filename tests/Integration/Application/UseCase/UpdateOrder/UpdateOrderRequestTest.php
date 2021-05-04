<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\UseCase\UpdateOrder;

use App\Application\Exception\RequestValidationException;
use App\Application\UseCase\UpdateOrder\UpdateOrderRequest;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\Tests\Helpers\FakeDataFiller;
use App\Tests\Helpers\RandomDataTrait;
use App\Tests\Integration\DatabaseTestCase;
use Ozean12\Money\TaxedMoney\TaxedMoneyFactory;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateOrderRequestTest extends DatabaseTestCase
{
    use ValidatedUseCaseTrait;
    use RandomDataTrait;
    use FakeDataFiller;

    /**
     * @test
     */
    public function validationShouldFailWhenBodyIsEmpty()
    {
        $order = $this->getRandomOrderEntity();
        $request = new UpdateOrderRequest($order->getUuid(), 1, null, null);

        $this->getRandomFinantialDetails(900);

        $validator = $this->getContainer()->get(ValidatorInterface::class);
        $this->setValidator($validator);

        $this->expectException(RequestValidationException::class);
        $this->validateRequest($request);
    }

    /**
     * @test
     */
    public function shouldFailWhenExternalCodeAlreadyExists()
    {
        $order = $this->getRandomOrderEntity('some_code');
        $request = new UpdateOrderRequest($order->getUuid(), $order->getMerchantId(), $order->getExternalCode(), null);

        $this->getRandomFinantialDetails(900);

        $validator = $this->getContainer()->get(ValidatorInterface::class);
        $this->setValidator($validator);

        $this->expectException(RequestValidationException::class);
        $this->validateRequest($request);
    }

    /**
     * @test
     */
    public function validationShouldFailWhenNewAmountIsMoreThanTheOrderAmount()
    {
        $order = $this->getRandomOrderEntity();
        $request = new UpdateOrderRequest($order->getUuid(), 1, null, TaxedMoneyFactory::create(1000, 999, 1));

        $this->getRandomFinantialDetails(900);

        $validator = $this->getContainer()->get(ValidatorInterface::class);
        $this->setValidator($validator);

        $this->expectException(RequestValidationException::class);
        $this->validateRequest($request);
    }

    /**
     * @test
     */
    public function validationShouldFailWhenNewAmountDoesNotMatchGross()
    {
        $order = $this->getRandomOrderEntity();
        $request = new UpdateOrderRequest($order->getUuid(), 1, null, TaxedMoneyFactory::create(1000, 300, 200));

        $this->getRandomFinantialDetails(900);

        $validator = $this->getContainer()->get(ValidatorInterface::class);
        $this->setValidator($validator);

        $this->expectException(RequestValidationException::class);
        $this->validateRequest($request);
    }
}
