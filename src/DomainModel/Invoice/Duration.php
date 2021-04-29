<?php

namespace App\DomainModel\Invoice;

use DateTime;

class Duration
{
    private int $days;

    public function __construct(int $days)
    {
        $this->setDuration($days);
    }

    public function days(): int
    {
        return $this->days;
    }

    private function setDuration(int $days): void
    {
        if ($days < 1 || $days > 120) {
            throw new InvalidDurationException('Invoice duration should be between 1 and 120 days');
        }
        $this->days = $days;
    }

    public function addToDate(DateTime $date): DateTime
    {
        $newDate = clone $date;

        return $newDate->modify('+' . $this->days . ' days');
    }
}
