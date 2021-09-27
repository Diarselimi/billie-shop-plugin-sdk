<?php

declare(strict_types=1);

namespace App\Application\UseCase\CheckoutDeclineOrder;

use App\Application\Tracking\TrackingEventCollector;
use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\WorkflowException;
use App\DomainEvent\AnalyticsEvent\TrackingItsNotUsEvent;
use App\DomainModel\Address\Address;
use App\DomainModel\Address\AddressEntity;
use App\DomainModel\Address\Exception\InvalidAddressException;
use App\DomainModel\CheckoutSession\CheckoutSessionRepositoryInterface;
use App\DomainModel\DebtorExternalData\DebtorExternalData;
use App\DomainModel\DebtorExternalData\DebtorExternalDataEntity;
use App\DomainModel\DebtorExternalData\DebtorExternalDataRepositoryInterface;
use App\DomainModel\Order\Lifecycle\DeclineOrderService;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderEntity;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Sepa\Client\DomainModel\SepaClientInterface;
use Ozean12\Support\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Component\Workflow\Registry;

class CheckoutDeclineOrderUseCase implements LoggingInterface
{
    use LoggingTrait;

    private Registry $workflowRegistry;

    private DeclineOrderService $declineOrderService;

    private OrderContainerFactory $orderContainerFactory;

    private CheckoutSessionRepositoryInterface $checkoutSessionRepository;

    private DebtorExternalDataRepositoryInterface $debtorExternalDataRepository;

    private SepaClientInterface $sepaClient;

    private TrackingEventCollector $eventsCollector;

    public function __construct(
        Registry $workflowRegistry,
        DeclineOrderService $declineOrderService,
        OrderContainerFactory $orderContainerFactory,
        CheckoutSessionRepositoryInterface $checkoutSessionRepository,
        DebtorExternalDataRepositoryInterface $debtorExternalDataRepository,
        SepaClientInterface $sepaClient,
        TrackingEventCollector $eventsCollector
    ) {
        $this->workflowRegistry = $workflowRegistry;
        $this->declineOrderService = $declineOrderService;
        $this->orderContainerFactory = $orderContainerFactory;
        $this->checkoutSessionRepository = $checkoutSessionRepository;
        $this->debtorExternalDataRepository = $debtorExternalDataRepository;
        $this->sepaClient = $sepaClient;
        $this->eventsCollector = $eventsCollector;
    }

    /**
     * @throws OrderNotFoundException
     */
    public function execute(CheckoutDeclineOrderRequest $input): void
    {
        try {
            $orderContainer = $this->orderContainerFactory->loadNotYetConfirmedByCheckoutSessionUuid($input->getSessionUuid());
        } catch (OrderContainerFactoryException $e) {
            throw new OrderNotFoundException($e);
        }

        $order = $orderContainer->getOrder();
        if (!$this->workflowRegistry->get($order)->can($order, OrderEntity::TRANSITION_DECLINE)) {
            throw new  WorkflowException('Order cannot be declined.');
        }

        $externalId = $orderContainer->getDebtorExternalData()->getMerchantExternalId();
        $this->declineOrderService->decline($orderContainer);
        $this->checkoutSessionRepository->reActivateSession($input->getSessionUuid());

        try {
            if ($order->getDebtorSepaMandateUuid() !== null) {
                $this->sepaClient->revokeMandate($order->getDebtorSepaMandateUuid());
            }
        } catch (HttpExceptionInterface $exception) {
            $this->logSuppressedException(
                $exception,
                sprintf('Mandate revoke call failed for uuid %s ', $order->getDebtorSepaMandateUuid())
            );
        }

        $this->logInfo("Decline checkout order triggered.", [
            LoggingInterface::KEY_UUID => $input->getSessionUuid(),
            LoggingInterface::KEY_SOBAKA => [
                'reason' => $input->getReason(),
                'merchant_external_id' => $externalId,
                'session_uuid' => $input->getSessionUuid(),
            ],
        ]);

        if (!$input->isWronglyIdentified()) {
            return;
        }

        $this->debtorExternalDataRepository->invalidateMerchantExternalId($externalId);

        $debtorExternalData = $orderContainer->getDebtorExternalData();
        $debtorExternalDataAddress = $orderContainer->getDebtorExternalDataAddress();

        try {
            $event = $this->prepareTrackingEvent($debtorExternalData, $debtorExternalDataAddress, $order, $input);
            $this->eventsCollector->collect($event);
        } catch (InvalidAddressException $e) {
            $this->logError(sprintf(
                'Tracking event %s failed because of not valid data, with the message %s',
                $event ? $event->getEventName() : 'tracking_event',
                $e->getMessage()
            ));
        }
    }

    private function prepareTrackingEvent(
        DebtorExternalDataEntity $debtorExternalData,
        AddressEntity $debtorExternalDataAddress,
        OrderEntity $order,
        CheckoutDeclineOrderRequest $input
    ): TrackingItsNotUsEvent {

        //TODO: Make VO's autoload from the database automatically, no need to fill the data like this.

        $externalData = new DebtorExternalData(
            $debtorExternalData->getName(),
            $debtorExternalData->getMerchantExternalId()
        );
        $address = new Address(
            $debtorExternalDataAddress->getStreet(),
            $debtorExternalDataAddress->getHouseNumber(),
            $debtorExternalDataAddress->getPostalCode(),
            $debtorExternalDataAddress->getCity(),
            $debtorExternalDataAddress->getCountry()
        );

        $event = new TrackingItsNotUsEvent(
            $order->getMerchantId(),
            $input->getSessionUuid(),
            $externalData,
            $address
        );

        return $event;
    }
}
