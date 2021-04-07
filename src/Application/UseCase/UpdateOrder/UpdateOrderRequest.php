<?php

declare(strict_types=1);

namespace App\Application\UseCase\UpdateOrder;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="UpdateOrderRequest", title="Order Update with invoice Object", type="object", properties={
 *      @OA\Property(property="amount", ref="#/components/schemas/AmountDTO"),
 *      @OA\Property(
 *          property="external_code",
 *          description="Update order request",
 *          ref="#/components/schemas/TinyText"
 *      )
 * })
 */
class UpdateOrderRequest
{
}
