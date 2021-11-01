<?php

declare(strict_types=1);

namespace App\Application\UseCase\OrderExpiration;

use App\Application\CommandHandler;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\UpdateOrderStateService;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsEntity;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsRepositoryInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Money\TaxedMoney\TaxedMoneyFactory;

class UpdateStateForExpiredOrdersCommandHandler implements CommandHandler, LoggingInterface
{
    use LoggingTrait;

    private OrderContainerFactory $containerFactory;

    private OrderFinancialDetailsRepositoryInterface $financialDetailsRepository;

    private OrderRepositoryInterface $orderRepository;

    private UpdateOrderStateService $orderStateService;

    public function __construct(
        OrderContainerFactory $containerFactory,
        OrderFinancialDetailsRepositoryInterface $financialDetailsRepository,
        OrderRepositoryInterface $orderRepository,
        UpdateOrderStateService $orderStateService
    ) {
        $this->containerFactory = $containerFactory;
        $this->financialDetailsRepository = $financialDetailsRepository;
        $this->orderRepository = $orderRepository;
        $this->orderStateService = $orderStateService;
    }

    public function execute(UpdateStateForExpiredOrdersCommand $input): void
    {
        $orders = $this->orderRepository->getOrdersUpToExpirationDateTime($input->getLimit());

        foreach ($orders as $order) {
            $orderContainer = $this->containerFactory->createFromOrderEntity($order);
            $this->logCurrentOrderState($orderContainer);
            $this->setUnshippedAmountToZero($orderContainer);

            $this->orderStateService->updateState($orderContainer);
            $this->logCurrentOrderState($orderContainer);
        }
    }

    private function setUnshippedAmountToZero(OrderContainer $orderContainer): void
    {
        $financialDetails = new OrderFinancialDetailsEntity(
            $orderContainer->getOrder(),
            $orderContainer->getOrderFinancialDetails()->getAmountTaxedMoney(),
            TaxedMoneyFactory::create(0, 0, 0),
            $orderContainer->getOrderFinancialDetails()->getDuration()
        );
        $this->financialDetailsRepository->insert($financialDetails);
        $orderContainer->setOrderFinancialDetails($financialDetails);
    }

    private function logCurrentOrderState(OrderContainer $orderContainer): void
    {
        $this->logInfo(
            sprintf(
                'Order {%s} expire date is passed, State:{%s}',
                $orderContainer->getOrder()->getId(),
                $orderContainer->getOrder()->getState()
            )
        );
    }
}
