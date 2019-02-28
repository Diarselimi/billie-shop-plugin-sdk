<?php

namespace App\DomainModel\Order;

class OrderStateCounterDTO
{
    private $total;

    private $totalActive;

    private $totalInactive;

    private $totalNew;

    private $totalCreated;

    private $totalDeclined;

    private $totalShipped;

    private $totalCanceled;

    private $totalLate;

    private $totalPaidOut;

    private $totalComplete;

    public function getTotal(): int
    {
        return $this->total;
    }

    public function setTotal(int $total): OrderStateCounterDTO
    {
        $this->total = $total;

        return $this;
    }

    public function getTotalActive(): int
    {
        return $this->totalActive;
    }

    public function setTotalActive(int $totalActive): OrderStateCounterDTO
    {
        $this->totalActive = $totalActive;

        return $this;
    }

    public function getTotalInactive(): int
    {
        return $this->totalInactive;
    }

    public function setTotalInactive(int $totalInactive): OrderStateCounterDTO
    {
        $this->totalInactive = $totalInactive;

        return $this;
    }

    public function getTotalNew(): int
    {
        return $this->totalNew;
    }

    public function setTotalNew(int $totalNew): OrderStateCounterDTO
    {
        $this->totalNew = $totalNew;

        return $this;
    }

    public function getTotalCreated(): int
    {
        return $this->totalCreated;
    }

    public function setTotalCreated(int $totalCreated): OrderStateCounterDTO
    {
        $this->totalCreated = $totalCreated;

        return $this;
    }

    public function getTotalDeclined(): int
    {
        return $this->totalDeclined;
    }

    public function setTotalDeclined(int $totalDeclined): OrderStateCounterDTO
    {
        $this->totalDeclined = $totalDeclined;

        return $this;
    }

    public function getTotalShipped(): int
    {
        return $this->totalShipped;
    }

    public function setTotalShipped(int $totalShipped): OrderStateCounterDTO
    {
        $this->totalShipped = $totalShipped;

        return $this;
    }

    public function getTotalCanceled(): int
    {
        return $this->totalCanceled;
    }

    public function setTotalCanceled(int $totalCanceled): OrderStateCounterDTO
    {
        $this->totalCanceled = $totalCanceled;

        return $this;
    }

    public function getTotalLate(): int
    {
        return $this->totalLate;
    }

    public function setTotalLate(int $totalLate): OrderStateCounterDTO
    {
        $this->totalLate = $totalLate;

        return $this;
    }

    public function getTotalPaidOut(): int
    {
        return $this->totalPaidOut;
    }

    public function setTotalPaidOut(int $totalPaidOut): OrderStateCounterDTO
    {
        $this->totalPaidOut = $totalPaidOut;

        return $this;
    }

    public function getTotalComplete(): int
    {
        return $this->totalComplete;
    }

    public function setTotalComplete(int $totalComplete): OrderStateCounterDTO
    {
        $this->totalComplete = $totalComplete;

        return $this;
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
