<?php

namespace spec\App\Http\RequestTransformer\CreateOrder;

use App\Application\UseCase\CreateOrder\Request\CreateOrderDebtorCompanyRequest;
use App\Application\UseCase\CreateOrder\Request\CreateOrderDebtorPersonRequest;
use App\DomainModel\CheckoutSession\CheckoutSessionEntity;
use App\DomainModel\Order\OrderEntity;
use App\Http\HttpConstantsInterface;
use App\Http\RequestTransformer\AmountRequestFactory;
use App\Http\RequestTransformer\CreateOrder\AddressRequestFactory;
use App\Http\RequestTransformer\CreateOrder\DebtorPersonRequestFactory;
use App\Http\RequestTransformer\CreateOrder\DebtorRequestFactory;
use App\Http\RequestTransformer\CreateOrder\OrderLineItemsRequestFactory;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;

class CreateOrderRequestFactorySpec extends ObjectBehavior
{
    public function let(
        DebtorRequestFactory $debtorRequestFactory,
        DebtorPersonRequestFactory $debtorPersonRequestFactory,
        AddressRequestFactory $addressRequestFactory,
        OrderLineItemsRequestFactory $lineItemsRequestFactory,
        AmountRequestFactory $amountRequestFactory
    ): void {
        $this->beConstructedWith(...func_get_args());
    }

    public function it_should_use_api_as_default_creation(
        DebtorRequestFactory $debtorRequestFactory,
        DebtorPersonRequestFactory $debtorPersonRequestFactory,
        OrderLineItemsRequestFactory $lineItemsRequestFactory,
        AmountRequestFactory $amountRequestFactory,
        TaxedMoney $taxedMoney
    ): void {
        $this->prepareCommonMocks(
            $debtorRequestFactory,
            $debtorPersonRequestFactory,
            $lineItemsRequestFactory,
            $amountRequestFactory,
            $taxedMoney
        );

        $request = Request::create('/');
        $this
            ->createForCreateOrder($request)
            ->getCreationSource()
            ->shouldBe(OrderEntity::CREATION_SOURCE_API);
    }

    public function it_should_override_creation_source(
        DebtorRequestFactory $debtorRequestFactory,
        DebtorPersonRequestFactory $debtorPersonRequestFactory,
        OrderLineItemsRequestFactory $lineItemsRequestFactory,
        AmountRequestFactory $amountRequestFactory,
        TaxedMoney $taxedMoney
    ): void {
        $this->prepareCommonMocks(
            $debtorRequestFactory,
            $debtorPersonRequestFactory,
            $lineItemsRequestFactory,
            $amountRequestFactory,
            $taxedMoney
        );

        $request = Request::create('/');
        $request->attributes->set(
            HttpConstantsInterface::REQUEST_ATTRIBUTE_CREATION_SOURCE,
            OrderEntity::CREATION_SOURCE_DASHBOARD
        );
        $this
            ->createForCreateOrder($request)
            ->getCreationSource()
            ->shouldBe(OrderEntity::CREATION_SOURCE_DASHBOARD);
    }

    private function prepareCommonMocks(
        DebtorRequestFactory $debtorRequestFactory,
        DebtorPersonRequestFactory $debtorPersonRequestFactory,
        OrderLineItemsRequestFactory $lineItemsRequestFactory,
        AmountRequestFactory $amountRequestFactory,
        TaxedMoney $taxedMoney
    ): void {
        $amountRequestFactory->create(Argument::any())->willReturn($taxedMoney);
        $debtorRequestFactory->create(Argument::any())->willReturn(new CreateOrderDebtorCompanyRequest());
        $debtorPersonRequestFactory->create(Argument::any())->willReturn(new CreateOrderDebtorPersonRequest());
        $lineItemsRequestFactory->create(Argument::any())->willReturn([]);
    }
}
