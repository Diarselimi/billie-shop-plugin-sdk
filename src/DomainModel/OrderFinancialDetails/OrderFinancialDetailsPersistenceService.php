<?php

namespace App\DomainModel\OrderFinancialDetails;

use App\Application\UseCase\LegacyUpdateOrder\UpdateOrderAmountInterface;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use Ozean12\Money\TaxedMoney\TaxedMoneyFactory;

class OrderFinancialDetailsPersistenceService
{
    private OrderFinancialDetailsRepositoryInterface $repository;

    private OrderFinancialDetailsFactory $factory;

    public function __construct(
        OrderFinancialDetailsRepositoryInterface $repository,
        OrderFinancialDetailsFactory $factory
    ) {
        $this->repository = $repository;
        $this->factory = $factory;
    }

    public function updateFinancialDetails(
        OrderContainer $orderContainer,
        UpdateOrderAmountInterface $changeSet,
        int $duration
    ): void {
        $financialDetails = $orderContainer->getOrderFinancialDetails();

        if ($changeSet->getAmount() !== null) {
            $amount = $changeSet->getAmount();
        } else {
            $amount = TaxedMoneyFactory::create(
                $financialDetails->getAmountGross(),
                $financialDetails->getAmountNet(),
                $financialDetails->getAmountTax()
            );
        }

        $unshippedAmount = new TaxedMoney(
            $financialDetails->getUnshippedAmountGross(),
            $financialDetails->getUnshippedAmountNet(),
            $financialDetails->getUnshippedAmountTax()
        );

        $newFinancialDetails = $this
            ->factory
            ->create($financialDetails->getOrderId(), $amount, $duration, $unshippedAmount)
        ;

        $this->repository->insert($newFinancialDetails);
        $orderContainer->setOrderFinancialDetails($newFinancialDetails);
    }
}
