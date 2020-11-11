<?php

namespace spec\App\DomainModel\OrderNotification;

use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderNotification\OrderNotificationEntity;
use App\Support\DateFormat;
use PhpSpec\ObjectBehavior;
use Webmozart\Assert\Assert;

class OrderNotificationPayloadFactorySpec extends ObjectBehavior
{
    public function let(
        OrderEntity $order
    ) {
        $order->getExternalCode()->shouldBeCalledOnce()->willReturn(11);
        $order->getUuid()->shouldBeCalledOnce()->willReturn(22);
    }

    public function it_should_create_order_event_payload(
        OrderEntity $order
    ) {
        $payload = $this->create($order, OrderNotificationEntity::NOTIFICATION_TYPE_PAYMENT)->getWrappedObject();

        Assert::isArray($payload);
        Assert::eq(
            $payload,
            [
                'created_at' => (new \DateTime())->format(DateFormat::FORMAT_YMD_HIS),
                'event' => 'payment',
                'order_id' => 11,
                'order_uuid' => 22,
            ]
        );
    }
}
