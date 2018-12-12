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

    // Returns the last processed order ID
    public function execute(int $limit, int $batchSize, int $sleepTime, int $lastId = 0, string $basePath = '/'): int
    {
        $orders = $this->orderRepository->getWithInvoiceNumber($limit, $lastId);

        foreach ($orders as $index => $orderRow) {
            if ($index !== 0 && $index % $batchSize === 0) {
                sleep($sleepTime);
            }

            /** @var array $orderRow */
            $merchantId = $orderRow['merchant_id'];
            $orderId = $orderRow['id'];
            $orderExternalCode = $orderRow['external_code'];

            if (!$this->eventPublisher->publish($orderExternalCode, $merchantId, $orderRow['invoice_number'], $basePath)) {
                throw new PaellaCoreCriticalException(
                    "Cannot publish invoice download event for order #" . $orderId . ". LastID was " . $lastId
                );
            }

            $lastId = $orderId;
        }

        return $lastId;
    }
}
