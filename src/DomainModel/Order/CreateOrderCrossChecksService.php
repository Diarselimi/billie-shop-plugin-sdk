<?php

namespace App\DomainModel\Order;

use App\Application\Exception\OrderWorkflowException;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsException;
use App\DomainModel\MerchantDebtor\Limits\MerchantDebtorLimitsService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class CreateOrderCrossChecksService implements LoggingInterface
{
    use LoggingTrait;

    private $merchantDebtorLimitsService;

    private $merchantRepository;

    public function __construct(
        MerchantDebtorLimitsService $merchantDebtorLimitsService,
        MerchantRepositoryInterface $merchantRepository
    ) {
        $this->merchantDebtorLimitsService = $merchantDebtorLimitsService;
        $this->merchantRepository = $merchantRepository;
    }

    public function run(OrderContainer $orderContainer): void
    {
        $merchant = $orderContainer->getMerchant();

        try {
            $this->merchantDebtorLimitsService->lock($orderContainer);
            $merchant->reduceFinancingLimit($orderContainer->getOrderFinancialDetails()->getAmountGross());
        } catch (MerchantDebtorLimitsException $exception) {
            $this->logSuppressedException($exception, 'Merchant debtor limit lock failed', [
                'exception' => $exception,
                'order_id' => $orderContainer->getOrder()->getId(),
            ]);

            throw new OrderWorkflowException("Create order cross checks failed", null, $exception);
        }

        $this->merchantRepository->update($merchant);
    }
}
