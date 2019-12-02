<?php

namespace App\Application\UseCase\CancelOrder;

class LimitUnlockException extends \RuntimeException
{
    protected $message = 'Limits cannot be unlocked';
}
