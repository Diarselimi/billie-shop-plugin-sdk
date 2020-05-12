<?php

namespace App\Amqp\Handler;

use App\Application\Exception\WorkflowException;
use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestApprover\DebtorInformationChangeRequestApproverException;
use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestDecisionIssuer;
use App\DomainModel\DebtorInformationChangeRequest\Exception\ChangeRequestNotFoundException;
use App\DomainModel\DebtorInformationChangeRequest\Exception\InvalidDecisionValueException;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Transfer\Message\CompanyInformationChangeRequest\CompanyInformationChangeRequestDecisionIssued;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class CompanyInformationChangeRequestDecisionIssuedHandler implements MessageHandlerInterface, LoggingInterface
{
    use LoggingTrait;

    private $decisionIssuer;

    public function __construct(DebtorInformationChangeRequestDecisionIssuer $decisionIssuer)
    {
        $this->decisionIssuer = $decisionIssuer;
    }

    public function __invoke(CompanyInformationChangeRequestDecisionIssued $message): void
    {
        try {
            $this->decisionIssuer->issueDecision($message);
        } catch (WorkflowException $exception) {
            $this->logWarning(sprintf(
                'Change request %s was declined or approved already',
                $message->getRequestUuid()
            ));
        } catch (
            DebtorInformationChangeRequestApproverException
            | ChangeRequestNotFoundException
            | InvalidDecisionValueException $exception
        ) {
            $this->logWarning($exception->getMessage());
        }
    }
}
