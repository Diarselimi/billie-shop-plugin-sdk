<?php

declare(strict_types=1);

namespace App\Application\UseCase\CheckoutConfirmOrder;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\RequestValidationException;
use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\CheckoutSession\CheckoutOrderMatcherInterface;
use App\DomainModel\CheckoutSession\CheckoutOrderRequestDTO;
use App\DomainModel\Order\Lifecycle\ApproveOrderService;
use App\DomainModel\Order\Lifecycle\WaitingOrderService;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderContainer\OrderContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainerFactoryException;
use App\DomainModel\Order\OrderRepository;

class CheckoutConfirmOrderUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private OrderContainerFactory $orderContainerFactory;

    private ApproveOrderService $approveOrderService;

    private WaitingOrderService $waitingOrderService;

    private CheckoutOrderMatcherInterface $dataMatcher;

    private OrderRepository $orderRepository;

    public function __construct(
        OrderContainerFactory $orderContainerFactory,
        ApproveOrderService $approveOrderService,
        WaitingOrderService $waitingOrderService,
        CheckoutOrderMatcherInterface $dataMatcher,
        OrderRepository $orderRepository
    ) {
        $this->orderContainerFactory = $orderContainerFactory;
        $this->approveOrderService = $approveOrderService;
        $this->waitingOrderService = $waitingOrderService;
        $this->dataMatcher = $dataMatcher;
        $this->orderRepository = $orderRepository;
    }

    public function execute(CheckoutConfirmOrderRequest $request): OrderContainer
    {
        $this->validateRequest($request);

        try {
            $orderContainer = $this->orderContainerFactory->loadNotYetConfirmedByCheckoutSessionUuid(
                $request->getSessionUuid()
            );
        } catch (OrderContainerFactoryException $exception) {
            throw new OrderNotFoundException($exception);
        }

        $this->assureDataMatches($request, $orderContainer);

        if ($request->getExternalCode() !== null) {
            $this->updateOrderExternalCode($request, $orderContainer);
        }

        if ($orderContainer->getOrder()->isPreWaiting()) {
            $this->waitingOrderService->wait($orderContainer);
        } else {
            $this->approveOrderService->approve($orderContainer);
        }

        return $orderContainer;
    }

    private function assureDataMatches(CheckoutConfirmOrderRequest $request, OrderContainer $orderContainer): void
    {
        $orderRequestDto = (new CheckoutOrderRequestDTO())
            ->setSessionUuid($request->getSessionUuid())
            ->setAmount($request->getAmount())
            ->setDebtorCompany($request->getDebtorCompanyRequest())
            ->setDeliveryAddress($request->getDeliveryAddress())
            ->setDuration($request->getDuration());

        $mismatchViolationList = $this->dataMatcher->matches($orderRequestDto, $orderContainer);

        if ($mismatchViolationList->hasMismatches()) {
            throw new RequestValidationException($mismatchViolationList);
        }
    }

    private function updateOrderExternalCode(CheckoutConfirmOrderRequest $request, OrderContainer $orderContainer): void
    {
        $orderContainer->getOrder()->setExternalCode($request->getExternalCode());
        $this->orderRepository->updateOrderExternalCode($orderContainer->getOrder());
    }
}
