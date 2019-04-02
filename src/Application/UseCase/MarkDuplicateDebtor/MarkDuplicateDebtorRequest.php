<?php

namespace App\Application\UseCase\MarkDuplicateDebtor;

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
