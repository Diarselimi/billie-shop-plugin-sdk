<?php

declare(strict_types=1);

namespace App\Application\UseCase\CreateCreditNote;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="CreateCreditNoteRequest", title="Create credit note request.",
 *     properties={
 *          @OA\Property(property="external_code", ref="#/components/schemas/TinyText", nullable=true),
 *          @OA\Property(property="amount", ref="#/components/schemas/AmountDTO"),
 *          @OA\Property(property="comment", ref="#/components/schemas/TinyText", nullable=true),
 *          @OA\Property(
 *              property="line_items",
 *              type="array",
 *              nullable=true,
 *              @OA\Items(ref="#/components/schemas/LineItemsRequest")
 *          )
 *     }
 * )
 */
class CreateCreditNoteRequest
{
}
