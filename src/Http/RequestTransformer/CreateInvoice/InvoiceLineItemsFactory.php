<?php

declare(strict_types=1);

namespace App\Http\RequestTransformer\CreateInvoice;

use App\Application\UseCase\LineItems\LineItemsRequest;
use Symfony\Component\HttpFoundation\Request;

class InvoiceLineItemsFactory
{
    public function createFromArray(array $data): LineItemsRequest
    {
        return new LineItemsRequest($data['external_code'] ?? null, $data['quantity'] ?? null);
    }

    public function create(Request $request): ?array
    {
        $lineItems = $request->request->get('line_items', null);

        if ($lineItems === null) {
            return null;
        }

        return array_map([$this, 'createFromArray'], $lineItems);
    }
}
