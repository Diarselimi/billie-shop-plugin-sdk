<?php

declare(strict_types=1);

namespace App\DomainModel\OrderNotification;

use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Order\OrderEntity;
use App\Support\DateFormat;

class OrderNotificationPayloadFactory
{
    public function create(?OrderEntity $order, ?Invoice $invoice, string $notificationType, array $extraPayload = []): array
    {
        $payload = [
            'created_at' => (new \DateTime())->format(DateFormat::FORMAT_YMD_HIS),
            'event' => $notificationType,
        ];

        if ($order !== null) {
            $payload = array_merge($payload, [
                'order_id' => $order->getExternalCode(),
                'order_uuid' => $order->getUuid(),
            ]);
        }

        if ($invoice !== null) {
            $payload = array_merge($payload, [
                'invoice_uuid' => $invoice->getUuid(),
            ]);
        }

        if (!empty($extraPayload)) {
            $payload = array_merge($payload, $extraPayload);
        }

        return $payload;
    }
}
