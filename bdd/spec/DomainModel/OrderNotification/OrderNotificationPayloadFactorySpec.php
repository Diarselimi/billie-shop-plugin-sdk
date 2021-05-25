<?php

namespace spec\App\DomainModel\OrderNotification;

use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\OrderNotification\OrderNotificationEntity;
use App\Support\DateFormat;
use PhpSpec\ObjectBehavior;
use Webmozart\Assert\Assert;

class OrderNotificationPayloadFactorySpec extends ObjectBehavior
{
    public function let(
        OrderEntity $order,
        Invoice $invoice
    ) {
        $order->getExternalCode()->shouldBeCalledOnce()->willReturn(11);
        $order->getUuid()->shouldBeCalledOnce()->willReturn('order_uuid');
        $invoice->getUuid()->shouldBeCalledOnce()->willReturn('invoice_uuid');
    }

    public function it_should_create_order_event_payload(
        OrderEntity $order,
        Invoice $invoice
    ) {
        $payload = $this->create($order, $invoice, OrderNotificationEntity::NOTIFICATION_TYPE_PAYMENT)->getWrappedObject();

        Assert::isArray($payload);
        Assert::eq(
            $payload,
            [
                'created_at' => (new \DateTime())->format(DateFormat::FORMAT_YMD_HIS),
                'event' => 'payment',
                'order_id' => 11,
                'order_uuid' => 'order_uuid',
                'invoice_uuid' => 'invoice_uuid',
            ]
        );
    }
}
