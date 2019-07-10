<?php

namespace App\Application\UseCase\MarkDuplicateDebtor;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="MarkDuplicateDebtorItem", required={"debtor_id", "is_duplicate_of"},
 *     properties={
 *          @OA\Property(property="debtor_id", type="integer"),
 *          @OA\Property(property="is_duplicate_of", type="integer"),
 *     }
 * )
 */
class MarkDuplicateDebtorRequest
{
    private $debtorId;

    private $isDuplicateOf;

    public function __construct(int $debtorId, int $isDuplicateOf)
    {
        $this->debtorId = $debtorId;
        $this->isDuplicateOf = $isDuplicateOf;
    }

    public function getDebtorId(): int
    {
        return $this->debtorId;
    }

    public function getIsDuplicateOf(): int
    {
        return $this->isDuplicateOf;
    }
}
