<?php

namespace App\Application\UseCase\PauseOrderDunning;

use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\SalesforceInterface;
use App\Infrastructure\Salesforce\Exception\SalesforceException;
use App\Infrastructure\Salesforce\Exception\SalesforcePauseDunningException;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class PauseOrderDunningUseCase implements ValidatedUseCaseInterface, LoggingInterface
{
    use ValidatedUseCaseTrait, LoggingTrait;

    private OrderRepositoryInterface $orderRepository;

    private SalesforceInterface $salesforce;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SalesforceInterface $salesforce
    ) {
        $this->orderRepository = $orderRepository;
        $this->salesforce = $salesforce;
    }

    public function execute(PauseOrderDunningRequest $request): void
    {
        $this->validateRequest($request);

        $order = $this->orderRepository->getOneByMerchantIdAndExternalCodeOrUUID($request->getOrderId(), $request->getMerchantId());

        if (!$order) {
            throw new OrderNotFoundException();
        }

        if (!$order->isLate()) {
            throw new PauseOrderDunningException('Cannot pause dunning. Order is not in state late');
        }

        try {
            $this->salesforce->pauseOrderDunning($order->getUuid(), $request->getNumberOfDays());
        } catch (SalesforcePauseDunningException $exception) {
            throw new PauseOrderDunningException($exception->getMessage());
        } catch (SalesforceException $exception) {
            throw new PauseOrderDunningUnhandledException($exception->getMessage());
        }
    }
}
