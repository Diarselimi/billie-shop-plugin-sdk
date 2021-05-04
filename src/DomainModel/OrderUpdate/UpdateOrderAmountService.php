<?php

declare(strict_types=1);

namespace App\DomainModel\OrderUpdate;

use App\Application\Exception\OrderBeingCollectedException;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\SalesforceInterface;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsEntity;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsRepositoryInterface;
use Ozean12\Money\TaxedMoney\TaxedMoney;

class UpdateOrderAmountService
{
    //TODO: Add the assertion inside the service instead of the validation.
    private SalesforceInterface $salesforce;

    private OrderFinancialDetailsRepositoryInterface $financialDetailsRepository;

    private UpdateOrderLimitsService $updateOrderLimitsService;

    public function __construct(
        SalesforceInterface $salesforce,
        OrderFinancialDetailsRepositoryInterface $financialDetailsRepository,
        UpdateOrderLimitsService $updateOrderLimitsService
    ) {
        $this->salesforce = $salesforce;
        $this->financialDetailsRepository = $financialDetailsRepository;
        $this->updateOrderLimitsService = $updateOrderLimitsService;
    }

    public function update(OrderContainer $orderContainer, TaxedMoney $newAmount): OrderFinancialDetailsEntity
    {
        $order = $orderContainer->getOrder();
        if ($order->isCanceled() || $order->isDeclined() || $order->isComplete()) {
            throw new UpdateOrderException(sprintf('Order in state %s cannot be updated.', $order->getState()));
        }

        if ($order->isLate() && $this->salesforce->getOrderCollectionsStatus($order->getUuid()) !== null) {
            throw new OrderBeingCollectedException();
        }

        if (!$this->isValid($orderContainer->getOrderFinancialDetails(), $newAmount)) {
            throw new UpdateOrderAmountException();
        }

        if (!$order->wasShipped()) {
            $this->updateOrderLimitsService->updateLimitAmounts($orderContainer, $newAmount);
        }

        $newFinancialDetails = clone $orderContainer->getOrderFinancialDetails();
        $calculatedTaxedMoney = new TaxedMoney(
            $newFinancialDetails->getUnshippedAmountGross()->subtract($newFinancialDetails->getAmountGross()->subtract($newAmount->getGross())),
            $newFinancialDetails->getUnshippedAmountNet()->subtract($newFinancialDetails->getAmountNet()->subtract($newAmount->getNet())),
            $newFinancialDetails->getUnshippedAmountTax()->subtract($newFinancialDetails->getAmountTax()->subtract($newAmount->getTax()))
        );

        $newFinancialDetails
            ->setUnshippedAmount($calculatedTaxedMoney)
            ->setAmount($newAmount)
            ->setCreatedAt($dateTime = new \DateTime())
            ->setUpdatedAt($dateTime);
        $this->financialDetailsRepository->insert($newFinancialDetails);

        $orderContainer->setOrderFinancialDetails($newFinancialDetails);

        return $newFinancialDetails;
    }

    private function isValid(OrderFinancialDetailsEntity $financialDetails, TaxedMoney $newAmount): bool
    {
        $amountDifferenceGross = $financialDetails->getAmountGross()->subtract($newAmount->getGross());

        return $amountDifferenceGross->greaterThan(0) &&
            $financialDetails->getUnshippedAmountGross()->greaterThanOrEqual($amountDifferenceGross) &&
            $financialDetails->getAmountGross()->greaterThanOrEqual($newAmount->getGross());
    }
}
