<?php

namespace spec\App\DomainModel\OrderResponse;

use App\DomainModel\Address\AddressEntityFactory;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\Order\OrderDeclinedReasonsMapper;
use App\DomainModel\Order\OrderStateManager;
use App\DomainModel\OrderResponse\OrderResponseFactory;
use App\DomainModel\Payment\PaymentsServiceInterface;
use PhpSpec\ObjectBehavior;

class OrderResponseFactorySpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(OrderResponseFactory::class);
    }

    public function let(
        CompaniesServiceInterface $companiesService,
        PaymentsServiceInterface $paymentsService,
        OrderStateManager $orderStateManager,
        OrderDeclinedReasonsMapper $declinedReasonsMapper,
        AddressEntityFactory $addressEntityFactory
    ) {
        $this->beConstructedWith(...func_get_args());
    }
}
