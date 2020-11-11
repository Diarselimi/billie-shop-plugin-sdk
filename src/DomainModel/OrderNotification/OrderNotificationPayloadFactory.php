<?php

declare(strict_types=1);

namespace App\DomainModel\OrderNotification;

use App\DomainModel\Order\OrderEntity;
use App\Support\DateFormat;

class OrderNotificationPayloadFactory
{
    public function create(OrderEntity $order, string $notificationType, array $extraPayload = []): array
    {
        $payload = [
            'created_at' => (new \DateTime())->format(DateFormat::FORMAT_YMD_HIS),
            'event' => $notificationType,
            'order_id' => $order->getExternalCode(),
            'order_uuid' => $order->getUuid(),
        ];

        if (!empty($extraPayload)) {
            $payload = array_merge($payload, $extraPayload);
        }

        return $payload;
    }
}
