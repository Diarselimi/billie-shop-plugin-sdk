<?php

declare(strict_types=1);

namespace App\DomainEvent\OrderRiskCheck;

use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\OrderRiskCheck\CheckResult;
use Symfony\Contracts\EventDispatcher\Event;

class RiskCheckResultEvent extends Event
{
    private $orderContainer;

    private $result;

    public function __construct(OrderContainer $orderContainer, CheckResult $result)
    {
        $this->orderContainer = $orderContainer;
        $this->result = $result;
    }

    public function getOrderContainer(): OrderContainer
    {
        return $this->orderContainer;
    }

    public function getResult(): CheckResult
    {
        return $this->result;
    }
}
