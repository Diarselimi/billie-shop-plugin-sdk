<?php

namespace App\DomainModel\OrderInvoice;

use App\DomainModel\Order\OrderEntity;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;

class OrderInvoiceManager implements LoggingInterface
{
    use LoggingTrait;

    private $uploadHandlers;

    /**
     * @param InvoiceUploadHandlerInterface[] $invoiceUploadHandlers
     */
    public function __construct(array $invoiceUploadHandlers)
    {
        $this->uploadHandlers = $invoiceUploadHandlers;
    }

    public function upload(OrderEntity $order, string $event): void
    {
        foreach ($this->uploadHandlers as $name => $handler) {
            if ($handler->supports($order->getMerchantId())) {
                $this->logInfo('Handling invoice {invoice_number} using {handler} handler', [
                    'invoice_number' => $order->getInvoiceNumber(),
                    'handler' => $name,
                ]);

                $handler->handleInvoice($order, $event);

                return;
            }
        }

        $this->logInfo('No supported handler for {invoice_number} found', [
            'invoice_number' => $order->getInvoiceNumber(),
        ]);

        throw new OrderInvoiceUploadException('No supported handler found');
    }
}
