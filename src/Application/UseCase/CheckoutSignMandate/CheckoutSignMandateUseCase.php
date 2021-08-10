<?php

declare(strict_types=1);

namespace App\Application\UseCase\CheckoutSignMandate;

use App\Application\Exception\OrderNotFoundException;
use App\DomainModel\CheckoutSession\CheckoutSessionRepositoryInterface;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderRepositoryInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Sepa\Client\DomainModel\Mandate\SepaMandateNotFoundException;
use Ozean12\Sepa\Client\DomainModel\SepaClientInterface;
use Ozean12\Support\HttpClient\Exception\HttpClientExceptionInterface;

class CheckoutSignMandateUseCase implements LoggingInterface
{
    use LoggingTrait;

    private OrderRepositoryInterface $orderRepository;

    private CheckoutSessionRepositoryInterface $checkoutSessionRepository;

    private SepaClientInterface $sepaClient;

    private OrderContainerFactory $orderContainerFactory;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        CheckoutSessionRepositoryInterface $checkoutSessionRepository,
        OrderContainerFactory $orderContainerFactory,
        SepaClientInterface $sepaMandateClient
    ) {
        $this->orderRepository = $orderRepository;
        $this->checkoutSessionRepository = $checkoutSessionRepository;
        $this->sepaClient = $sepaMandateClient;
        $this->orderContainerFactory = $orderContainerFactory;
    }

    public function execute(CheckoutSignMandateRequest $request): void
    {
        try {
            $orderContainer = $this->orderContainerFactory->loadNotYetConfirmedByCheckoutSessionUuid($request->getSessionUuid());
            $order = $orderContainer->getOrder();
        } catch (OrderContainerFactoryException $exception) {
            throw new OrderNotFoundException($exception);
        }

        if ($order->getDebtorSepaMandateUuid() === null) {
            throw new SepaMandateNotFoundException();
        }

        try {
            $this->sepaClient->signMandate($order->getDebtorSepaMandateUuid(), new \DateTime());
        } catch (HttpClientExceptionInterface $exception) {
            $this->logSuppressedException($exception, 'Sign mandate call failed.');
        }
    }
}
