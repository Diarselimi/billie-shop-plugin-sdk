<?php

namespace App\DomainModel\DebtorInformationChangeRequest;

use App\DomainModel\DebtorInformationChangeRequest\Exception\ChangeRequestNotFoundException;
use App\DomainModel\DebtorInformationChangeRequest\Exception\InvalidDecisionValueException;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Transfer\Message\CompanyInformationChangeRequest\CompanyInformationChangeRequestDecisionIssued;
use Symfony\Component\Workflow\Workflow;

class DebtorInformationChangeRequestDecisionIssuer implements LoggingInterface
{
    use LoggingTrait;

    private $workflow;

    private $repository;

    public function __construct(
        Workflow $debtorInformationChangeRequestWorkflow,
        DebtorInformationChangeRequestRepositoryInterface $repository
    ) {
        $this->workflow = $debtorInformationChangeRequestWorkflow;
        $this->repository = $repository;
    }

    public function issueDecision(CompanyInformationChangeRequestDecisionIssued $message): void
    {
        $changeRequest = $this->repository->getOneByUuid($message->getRequestUuid());
        if (!$changeRequest) {
            throw new ChangeRequestNotFoundException(
                sprintf('Change request with uuid %s not found', $message->getRequestUuid())
            );
        }

        switch ($message->getDecision()) {
            case 'approved':
                $transitionName = DebtorInformationChangeRequestTransitionEntity::TRANSITION_COMPLETE_MANUALLY;

                break;
            case 'declined':
                $transitionName = DebtorInformationChangeRequestTransitionEntity::TRANSITION_DECLINE_MANUALLY;

                break;
            default:
                throw new InvalidDecisionValueException(sprintf(
                    'Invalid decision value "%s" for change request with uuid %s',
                    $message->getDecision(),
                    $message->getRequestUuid()
                ));
        }

        $this->workflow->apply($changeRequest, $transitionName);
        $this->repository->update($changeRequest);

        $this->logInfo(
            'Debtor information change request {id} decision issued',
            ['id' => $changeRequest->getId()]
        );
    }
}
