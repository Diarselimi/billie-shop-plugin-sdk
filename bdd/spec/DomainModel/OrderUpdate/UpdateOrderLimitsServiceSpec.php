<?php

namespace spec\App\DomainModel\OrderUpdate;

use App\Application\UseCase\UpdateOrderWithInvoice\UpdateOrderWithInvoiceRequest;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsEntity;
use Ozean12\Money\Money;
use Ozean12\Money\TaxedMoney\TaxedMoneyFactory;
use PhpSpec\ObjectBehavior;

class UpdateOrderLimitsServiceSpec extends ObjectBehavior
{
    public function let(
        MerchantRepositoryInterface $merchantRepository,
        MerchantDebtorLimitsService $merchantDebtorLimitsService
    ) {
        $this->beConstructedWith(...func_get_args());
    }

    public function it_unlocks_debtor_and_merchant_limit(
        OrderContainer $orderContainer,
        MerchantDebtorLimitsService $merchantDebtorLimitsService,
        MerchantEntity $merchant,
        MerchantRepositoryInterface $merchantRepository
    ) {
        // Arrange
        $changeSet = (new UpdateOrderWithInvoiceRequest('', 1))
            ->setAmount(TaxedMoneyFactory::create(5.0, 0.0, 0.0));
        $financialDetails = (new OrderFinancialDetailsEntity())
            ->setAmountGross(new Money(2.0));
        $orderContainer
            ->getOrderFinancialDetails()
            ->willReturn($financialDetails);
        $orderContainer
            ->getMerchant()
            ->willReturn($merchant);

        // Assert
        $merchantDebtorLimitsService
            ->unlock($orderContainer, new Money(-3.0))
            ->shouldBeCalledOnce();
        $merchant
            ->increaseFinancingLimit(new Money(-3.0))
            ->shouldBeCalledOnce();
        $merchantRepository
            ->update($merchant)
            ->shouldBeCalledOnce();

        // Act
        $this->updateLimitAmounts($orderContainer, $changeSet->getAmount()->getGross());
    }
}
