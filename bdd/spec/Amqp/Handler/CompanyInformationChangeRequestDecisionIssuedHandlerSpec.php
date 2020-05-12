<?php

namespace spec\App\Amqp\Handler;

use App\Application\Exception\WorkflowException;
use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestApprover\DebtorInformationChangeRequestApproverException;
use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestDecisionIssuer;
use App\DomainModel\DebtorInformationChangeRequest\Exception\ChangeRequestNotFoundException;
use App\DomainModel\DebtorInformationChangeRequest\Exception\InvalidDecisionValueException;
use Ozean12\Transfer\Message\CompanyInformationChangeRequest\CompanyInformationChangeRequestDecisionIssued;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

class CompanyInformationChangeRequestDecisionIssuedHandlerSpec extends ObjectBehavior
{
    public function let(
        DebtorInformationChangeRequestDecisionIssuer $decisionIssuer,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith(...func_get_args());
        $this->setLogger($logger);
    }

    public function it_should_catch_exceptions(
        DebtorInformationChangeRequestDecisionIssuer $decisionIssuer
    ) {
        $message = new CompanyInformationChangeRequestDecisionIssued();
        foreach ([
            WorkflowException::class,
            DebtorInformationChangeRequestApproverException::class,
            ChangeRequestNotFoundException::class,
            InvalidDecisionValueException::class,
        ] as $exceptionClassName) {
            $decisionIssuer
                ->issueDecision($message)
                ->willThrow($exceptionClassName);
            $this->__invoke($message);
        }
    }
}
