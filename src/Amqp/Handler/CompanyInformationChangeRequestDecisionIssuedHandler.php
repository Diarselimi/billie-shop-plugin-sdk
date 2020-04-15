<?php

namespace App\Amqp\Handler;

use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestDecisionIssuer;
use Ozean12\Transfer\Message\CompanyInformationChangeRequest\CompanyInformationChangeRequestDecisionIssued;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class CompanyInformationChangeRequestDecisionIssuedHandler implements MessageHandlerInterface
{
    private $decisionIssuer;

    public function __construct(DebtorInformationChangeRequestDecisionIssuer $decisionIssuer)
    {
        $this->decisionIssuer = $decisionIssuer;
    }

    public function __invoke(CompanyInformationChangeRequestDecisionIssued $message): void
    {
        $this->decisionIssuer->issueDecision($message);
    }
}
