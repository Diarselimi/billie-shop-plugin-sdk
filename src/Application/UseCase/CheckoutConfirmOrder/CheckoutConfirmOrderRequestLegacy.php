<?php

declare(strict_types=1);

namespace App\Application\UseCase\CheckoutConfirmOrder;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="CheckoutConfirmOrderRequestLegacy", required={"amount", "duration", "debtor_company"}, properties={
 *      @OA\Property(property="amount", ref="#/components/schemas/AmountDTO"),
 *      @OA\Property(property="duration", ref="#/components/schemas/OrderDuration"),
 *      @OA\Property(property="debtor_company", ref="#/components/schemas/DebtorCompanyRequestLegacy"),
 *      @OA\Property(property="delivery_address", ref="#/components/schemas/CreateOrderAddressRequest", nullable=true),
 *      @OA\Property(property="order_id", ref="#/components/schemas/TinyText", description="Order external code", example="DE123456-1")
 * })
 */
class CheckoutConfirmOrderRequestLegacy extends CheckoutConfirmOrderRequest
{
}
