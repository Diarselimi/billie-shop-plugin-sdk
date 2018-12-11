<?php

namespace App\Application\UseCase\MarkOrderAsFraud;

class FraudReclaimActionException extends \Exception
{
    protected $message = "No fraud reclaim action occurred, criteria wasn't met";
}
