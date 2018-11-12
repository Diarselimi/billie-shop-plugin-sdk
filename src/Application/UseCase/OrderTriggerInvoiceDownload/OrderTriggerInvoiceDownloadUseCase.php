<?php

namespace App\Application\UseCase\OrderTriggerInvoiceDownload;

use App\Application\PaellaCoreCriticalException;
use App\DomainModel\Monitoring\LoggingInterface;
use App\DomainModel\Monitoring\LoggingTrait;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\Infrastructure\Sns\InvoiceDownloadEventPublisherInterface;

class OrderTriggerInvoiceDownloadUseCase implements LoggingInterface
{
    use LoggingTrait;

    private $orderRepository;

    private $eventPublisher;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        InvoiceDownloadEventPublisherInterface $eventPublisher
    ) {
        $this->orderRepository = $orderRepository;
        $this->eventPublisher = $eventPublisher;
    }

    /**
     * @return int The last processed order ID
     */
    public function execute(int $limit, int $lastId = 0): int
    {
        $orders = $this->orderRepository->getWithInvoiceNumber($limit, $lastId);

        foreach ($orders as $orderRow) {
            /** @var array $orderRow */
            $merchantId = $orderRow['merchant_id'];
            $orderId = $orderRow['id'];

            if (!$this->eventPublisher->publish($orderId, $merchantId, $orderRow['invoice_number'])) {
                throw new PaellaCoreCriticalException(
                    "Cannot publish invoice download event for order #" . $orderId . ". LastID was " . $lastId
                );
            }

            $lastId = $orderId;
        }

        return $lastId;
    }
}
