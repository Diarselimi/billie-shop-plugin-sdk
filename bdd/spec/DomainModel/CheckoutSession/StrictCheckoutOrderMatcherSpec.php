<?php

namespace spec\App\DomainModel\CheckoutSession;

use App\Application\UseCase\CreateOrder\Request\CreateOrderAddressRequest;
use App\DomainModel\Address\AddressEntity;
use App\DomainModel\CheckoutSession\CheckoutOrderRequestDTO;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\CompanyRequestFactory;
use App\DomainModel\DebtorCompany\DebtorCompanyRequest;
use App\DomainModel\DebtorExternalData\DebtorExternalDataEntity;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsEntity;
use App\Infrastructure\Alfred\Dto\StrictMatchRequestDTO;
use Ozean12\Money\Money;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class StrictCheckoutOrderMatcherSpec extends ObjectBehavior
{
    public function let(
        CompaniesServiceInterface $companiesService,
        CompanyRequestFactory $requestFactory,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith($companiesService, $requestFactory);
        $this->setLogger($logger);

        $requestFactory->createCompanyStrictMatchRequestDTO(Argument::cetera())->willReturn(new StrictMatchRequestDTO([1], []));
        $requestFactory->createCompanyStrictMatchRequestDTOFromAddress(Argument::cetera())->willReturn(new StrictMatchRequestDTO([2], []));
    }

    public function it_should_return_false_when_billing_address_does_not_match(
        CheckoutOrderRequestDTO $requestDTO,
        OrderContainer $orderContainer,
        OrderFinancialDetailsEntity $orderFinancialDetails,
        DebtorExternalDataEntity $debtorExternalDataEntity,
        CompaniesServiceInterface $companiesService,
        TaxedMoney $taxedMoney,
        DebtorCompanyRequest $companyRequest,
        AddressEntity $addressEntity,
        CreateOrderAddressRequest $addressRequest,
        OrderEntity $orderEntity
    ) {
        $orderFinancialDetails->getAmountNet()->willReturn(new Money(4));
        $orderFinancialDetails->getAmountTax()->willReturn(new Money(4));
        $orderFinancialDetails->getAmountGross()->willReturn(new Money(8));

        $requestDTO->getAmount()->willReturn($taxedMoney);
        $taxedMoney->getNet()->willReturn(new Money(4));
        $taxedMoney->getTax()->willReturn(new Money(4));
        $taxedMoney->getGross()->willReturn(new Money(8));

        $requestDTO->getDuration()->willReturn(1);
        $orderFinancialDetails->getDuration()->willReturn(1);

        $requestDTO->getDebtorCompany()->willReturn($companyRequest);
        $orderContainer->getDebtorExternalDataAddress()->willReturn($addressEntity);

        $orderContainer->getOrderFinancialDetails()->willReturn($orderFinancialDetails);

        $debtorExternalDataEntity->getName()->willReturn('company name');
        $orderContainer->getDebtorExternalData()->willReturn($debtorExternalDataEntity);
        $orderContainer->getOrder()->willReturn($orderEntity);

        $companiesService->strictMatchDebtor(new StrictMatchRequestDTO([1], []))->willReturn(true);
        $companiesService->strictMatchDebtor(new StrictMatchRequestDTO([2], []))->willReturn(false);

        $requestDTO->getDeliveryAddress()->willReturn($addressRequest);
        $orderContainer->getDeliveryAddress()->willReturn($addressEntity);

        $this->matches($requestDTO, $orderContainer)->shouldBe(false);
    }
}
