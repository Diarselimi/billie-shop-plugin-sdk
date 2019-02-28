<?php

namespace App\DomainModel\MerchantDebtor;

use App\DomainModel\Order\OrderStateCounterDTO;

class MerchantDebtorDuplicateDTO extends MerchantDebtorIdentifierDTO
{
    private $parentMerchantDebtorId;

    private $parentDebtorId;

    private $duplicationCategory;

    private $markAsDuplicate;

    private $orderStateCounter;

    public function __construct()
    {
        $this->orderStateCounter = new OrderStateCounterDTO();
    }

    public function getParentMerchantDebtorId(): ?int
    {
        return $this->parentMerchantDebtorId;
    }

    public function setParentMerchantDebtorId(?int $parentMerchantDebtorId): MerchantDebtorDuplicateDTO
    {
        $this->parentMerchantDebtorId = $parentMerchantDebtorId;

        return $this;
    }

    public function getParentDebtorId(): ?int
    {
        return $this->parentDebtorId;
    }

    public function setParentDebtorId(?int $parentDebtorId): MerchantDebtorDuplicateDTO
    {
        $this->parentDebtorId = $parentDebtorId;

        return $this;
    }

    public function getDuplicationCategory(): int
    {
        return $this->duplicationCategory;
    }

    public function setDuplicationCategory(int $duplicationCategory): MerchantDebtorDuplicateDTO
    {
        $this->duplicationCategory = $duplicationCategory;

        return $this;
    }

    public function isMarkAsDuplicate(): bool
    {
        return $this->markAsDuplicate;
    }

    public function setMarkAsDuplicate(bool $markAsDuplicate): MerchantDebtorDuplicateDTO
    {
        $this->markAsDuplicate = $markAsDuplicate;

        return $this;
    }

    public function getOrderStateCounter(): OrderStateCounterDTO
    {
        return $this->orderStateCounter;
    }

    public function setOrderStateCounter(OrderStateCounterDTO $orderStateCounter): MerchantDebtorDuplicateDTO
    {
        $this->orderStateCounter = $orderStateCounter;

        return $this;
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
