<?php

namespace spec\App\Amqp\Handler;

use App\Application\Exception\WorkflowException;
use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestApprover\DebtorInformationChangeRequestApproverException;
use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestDecisionIssuer;
use App\DomainModel\DebtorInformationChangeRequest\Exception\ChangeRequestNotFoundException;
use App\DomainModel\DebtorInformationChangeRequest\Exception\InvalidDecisionValueException;
use Billie\MonitoringBundle\Service\Alerting\Sentry\Raven\RavenClient;
use Ozean12\Transfer\Message\CompanyInformationChangeRequest\CompanyInformationChangeRequestDecisionIssued;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;

class CompanyInformationChangeRequestDecisionIssuedHandlerSpec extends ObjectBehavior
{
    public function let(
        DebtorInformationChangeRequestDecisionIssuer $decisionIssuer,
        LoggerInterface $logger,
        RavenClient $sentry
    ) {
        $this->beConstructedWith(...func_get_args());
        $this->setLogger($logger);
        $this->setSentry($sentry);
    }

    public function it_should_catch_exceptions(
        DebtorInformationChangeRequestDecisionIssuer $decisionIssuer,
        RavenClient $sentry
    ) {
        $message = new CompanyInformationChangeRequestDecisionIssued();
        foreach ([
            WorkflowException::class,
            DebtorInformationChangeRequestApproverException::class,
            ChangeRequestNotFoundException::class,
            InvalidDecisionValueException::class,
        ] as $exceptionClassName) {
            $sentry->captureException(Argument::any())->shouldBeCalled();

            $decisionIssuer
                ->issueDecision($message)
                ->willThrow($exceptionClassName);
            $this->__invoke($message);
        }
    }
}
