<?php

namespace App\Application\UseCase\PauseOrderDunning;

use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\Order\SalesforceInterface;
use App\DomainModel\Salesforce\PauseDunningRequestBuilder;
use App\Infrastructure\Salesforce\Exception\SalesforceException;
use App\Infrastructure\Salesforce\Exception\SalesforcePauseDunningException;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class PauseOrderDunningUseCase implements ValidatedUseCaseInterface, LoggingInterface
{
    use ValidatedUseCaseTrait, LoggingTrait;

    private OrderRepositoryInterface $orderRepository;

    private SalesforceInterface $salesforce;

    private OrderContainerFactory $orderContainerFactory;

    public function __construct(
        SalesforceInterface $salesforce,
        OrderContainerFactory $orderContainerFactory
    ) {
        $this->salesforce = $salesforce;
        $this->orderContainerFactory = $orderContainerFactory;
    }

    public function execute(PauseOrderDunningRequest $request): void
    {
        $this->validateRequest($request);

        try {
            $orderContainer = $this->orderContainerFactory->loadByMerchantIdAndExternalIdOrUuid(
                $request->getMerchantId(),
                $request->getOrderId()
            );
        } catch (OrderContainerFactoryException $e) {
            throw new OrderNotFoundException();
        }

        $order = $orderContainer->getOrder();
        if (!$order->isLate()) {
            throw new PauseOrderDunningException('Cannot pause dunning. Order is not in state late');
        }

        $invoiceUuid = null;
        if (!$orderContainer->getInvoices()->isEmpty()) {
            $invoiceUuid = $orderContainer->getInvoices()->getLastInvoice()->getUuid();
        }

        try {
            $dunningRequestBuilder = new PauseDunningRequestBuilder(
                $order->getUuid(),
                $invoiceUuid,
                $request->getNumberOfDays()
            );
            $this->salesforce->pauseDunning($dunningRequestBuilder);
        } catch (SalesforcePauseDunningException $exception) {
            throw new PauseOrderDunningException($exception->getMessage());
        } catch (SalesforceException $exception) {
            throw new PauseOrderDunningUnhandledException($exception->getMessage());
        }
    }
}
