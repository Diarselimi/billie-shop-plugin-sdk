<?php

declare(strict_types=1);

namespace App\Application\UseCase\CheckoutProvideIban;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\WorkflowException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Iban\IbanFraudCheck;
use App\DomainModel\Mandate\GenerateMandateException;
use App\DomainModel\Mandate\SepaMandateGenerator;
use App\DomainModel\Order\Lifecycle\DeclineOrderService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Support\ValueObject\Iban;
use Symfony\Component\Workflow\Registry;

final class CheckoutProvideIbanUseCase implements LoggingInterface, ValidatedUseCaseInterface
{
    use LoggingTrait,
        ValidatedUseCaseTrait;

    private const CREDITOR_NAME = 'Billie GmbH';

    private OrderContainerFactory $orderContainerFactory;

    private OrderRepositoryInterface $orderRepository;

    private IbanFraudCheck $ibanFraudCheck;

    private Registry $workflowRegistry;

    private DeclineOrderService $declineOrderService;

    private SepaMandateGenerator $sepaMandateGenerator;

    public function __construct(
        OrderContainerFactory $orderContainerFactory,
        OrderRepositoryInterface $orderRepository,
        IbanFraudCheck $ibanFraudCheck,
        Registry $workflowRegistry,
        DeclineOrderService $declineOrderService,
        SepaMandateGenerator $sepaMandateGenerator
    ) {
        $this->orderContainerFactory = $orderContainerFactory;
        $this->orderRepository = $orderRepository;
        $this->ibanFraudCheck = $ibanFraudCheck;
        $this->workflowRegistry = $workflowRegistry;
        $this->declineOrderService = $declineOrderService;
        $this->sepaMandateGenerator = $sepaMandateGenerator;
    }

    public function execute(CheckoutProvideIbanRequest $input): CheckoutProvideIbanResponse
    {
        try {
            $orderContainer = $this->orderContainerFactory->loadNotYetConfirmedByCheckoutSessionUuid($input->getSessionUuid());
        } catch (OrderContainerFactoryException $exception) {
            throw new OrderNotFoundException($exception);
        }

        $this->validateRequest($input);

        $order = $orderContainer->getOrder();
        $iban = new Iban($input->getIban());

        $this->declineIfIbanFraud($iban, $orderContainer);

        try {
            $mandate = $this->sepaMandateGenerator->generateForOrderContainer($orderContainer, $iban);
        } catch (GenerateMandateException $exception) {
            throw new CheckoutProvideIbanFailedException("SEPA Mandate can't be generated: " . $exception->getMessage(), 0, $exception);
        }

        $order->setDebtorSepaMandateUuid($mandate->getUuid());
        $this->orderRepository->update($order);

        return new CheckoutProvideIbanResponse($mandate, self::CREDITOR_NAME);
    }

    private function declineIfIbanFraud(Iban $iban, OrderContainer $orderContainer): void
    {
        $order = $orderContainer->getOrder();
        if ($this->ibanFraudCheck->check($iban, $order)) {
            return;
        }

        if ($this->workflowRegistry->get($order)->can($order, OrderEntity::TRANSITION_DECLINE)) {
            $this->declineOrderService->decline($orderContainer);
        } else {
            $message = 'Cannot decline the order after IBAN fraud check. Order is ' . $order->getState();
            $this->logSuppressedException(new WorkflowException($message), $message);
        }

        throw new CheckoutProvideIbanNotAllowedException();
    }
}
