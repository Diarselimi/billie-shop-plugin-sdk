<?php

namespace Billie\Sdk\Tests\Acceptance\Model\Request;

use Billie\Sdk\Model\Request\ShipOrderRequestModel;
use Billie\Sdk\Tests\Acceptance\Model\AbstractModelTestCase;

class ShipOrderRequestModelTest extends AbstractModelTestCase
{
    public function testToArray()
    {
        $data = (new ShipOrderRequestModel('uuid'))
            ->setExternalOrderId('external-order-id')
            ->setInvoiceNumber('123456789')
            ->setShippingDocumentUrl('https://domain.com/path/shipping.pdf')
            ->setInvoiceUrl('https://domain.com/path/invoice.pdf')
            ->toArray();

        static::assertCount(4, $data); // uuid should not be returned
        static::assertEquals('external-order-id', $data['external_order_id']);
        static::assertEquals('123456789', $data['invoice_number']);
        static::assertEquals('https://domain.com/path/shipping.pdf', $data['shipping_document_url']);
        static::assertEquals('https://domain.com/path/invoice.pdf', $data['invoice_url']);
    }
}
