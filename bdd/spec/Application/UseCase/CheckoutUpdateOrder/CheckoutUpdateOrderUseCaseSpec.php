<?php

declare(strict_types=1);

namespace spec\App\Application\UseCase\CheckoutUpdateOrder;

use App\Application\UseCase\CheckoutUpdateOrder\CheckoutUpdateOrderRequest;
use App\Application\UseCase\CheckoutUpdateOrder\CheckoutUpdateOrderUseCase;
use App\DomainModel\Address\AddressRepositoryInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorExternalData\DebtorExternalDataRepositoryInterface;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\Infrastructure\Repository\OrderFinancialDetailsRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CheckoutUpdateOrderUseCaseSpec extends ObjectBehavior
{
    public function let(
        OrderContainerFactory $orderContainerFactory,
        CompaniesServiceInterface $companiesService,
        AddressRepositoryInterface $addressRepository,
        DebtorExternalDataRepositoryInterface $debtorExternalDataRepository,
        OrderRepositoryInterface $orderRepository,
        OrderFinancialDetailsRepository $financialDetailsRepository,
        ValidatorInterface $validator
    ) {
        $this->beConstructedWith(...func_get_args());

        $this->setValidator($validator);
        $validator->validate(Argument::any(), Argument::any(), Argument::any())
            ->willReturn(new ConstraintViolationList());
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(CheckoutUpdateOrderUseCase::class);
    }

    public function it_should_save_duration_to_database(
        OrderContainerFactory $orderContainerFactory,
        OrderContainer $orderContainer,
        OrderFinancialDetailsRepository $financialDetailsRepository,
        CheckoutUpdateOrderRequest $request
    ) {
        $request->getDuration()->willReturn(30);
        $orderContainerFactory->loadNotYetConfirmedByCheckoutSessionUuid(Argument::any())->willReturn($orderContainer);
    }
}
