<?php

namespace App\DomainModel\OrderFinancialDetails;

use App\Application\UseCase\UpdateOrder\UpdateOrderAmountInterface;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use Ozean12\Money\TaxedMoney\TaxedMoneyFactory;

class OrderFinancialDetailsPersistenceService
{
    private $repository;

    private $factory;

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
    ) {
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

        $newFinancialDetails = $this
            ->factory
            ->create($financialDetails->getOrderId(), $amount, $duration);

        $this->repository->insert($newFinancialDetails);
        $orderContainer->setOrderFinancialDetails($newFinancialDetails);
    }
}
