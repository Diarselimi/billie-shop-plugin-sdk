<?php

declare(strict_types=1);

namespace App\Http\Controller;

use App\Http\HttpConstantsInterface;
use Symfony\Component\HttpFoundation\Request;

trait MerchantIdTrait
{
    private function getMerchantId(Request $request): int
    {
        return $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID);
    }
}
