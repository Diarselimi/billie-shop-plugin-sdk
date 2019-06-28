<?php

namespace App\Application\Exception;

class OrderDeclinedException extends \RuntimeException
{
    protected $message = 'Order is declined.';

    protected $reasons = [];

    public function __construct(array $reasons)
    {
        $this->reasons = $reasons;
        parent::__construct($this->message);
    }

    public function getReasons(): array
    {
        return $this->reasons;
    }
}
