<?php

declare(strict_types=1);

namespace App\Http\RequestTransformer\CreateInvoice;

use App\DomainModel\Invoice\ShippingInfo;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\Request;

class ShippingInfoFactory
{
    public function create(Request $request, UuidInterface $invoiceUuid): ShippingInfo
    {
        if ($request->request->has('shipping_info')) {
            $shippingInfo = $request->get('shipping_info', []);
        } else {
            $shippingInfo = [
                'tracking_url' => $request->get('shipping_document_url'),
            ];
        }

        return new ShippingInfo(
            $invoiceUuid,
            $shippingInfo['tracking_url'] ?? null,
            $shippingInfo['tracking_number'] ?? null,
            $shippingInfo['shipping_method'] ?? null,
            $shippingInfo['shipping_company'] ?? null,
            $shippingInfo['return_tracking_number'] ?? null,
            $shippingInfo['return_tracking_url'] ?? null,
            $shippingInfo['return_shipping_company'] ?? null,
        );
    }
}
