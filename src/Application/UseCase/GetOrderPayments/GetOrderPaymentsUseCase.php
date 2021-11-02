<?php

namespace App\Application\UseCase\GetOrderPayments;

use App\Application\Exception\OrderNotFoundException;
use App\DomainModel\Order\OrderRepository;
use App\DomainModel\OrderPayments\OrderPaymentsResponseTransformer;
use App\DomainModel\Payment\PaymentsRepositoryInterface;
use App\Support\PaginatedCollection;

/**
 * @deprecated replaced by GetInvoicePaymentsUseCase
 */
class GetOrderPaymentsUseCase
{
    private $orderRepository;

    private $paymentsRepository;

    private $orderPaymentsTransformer;

    public function __construct(
        OrderRepository $orderRepository,
        PaymentsRepositoryInterface $paymentsRepository,
        OrderPaymentsResponseTransformer $orderPaymentsTransformer
    ) {
        $this->orderRepository = $orderRepository;
        $this->paymentsRepository = $paymentsRepository;
        $this->orderPaymentsTransformer = $orderPaymentsTransformer;
    }

    public function execute(string $uuid): PaginatedCollection
    {
        $order = $this->orderRepository->getOneByUuid($uuid);

        if (!$order) {
            throw new OrderNotFoundException();
        }

        $result = new PaginatedCollection([], 0);
        if ($order->getPaymentId()) {
            $result = $this->paymentsRepository->getTicketPayments($order->getPaymentId());
        }

        return $this->orderPaymentsTransformer->transformPaymentsCollection($result);
    }
}
