<?php

namespace App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestApprover;

use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestEntity;
use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestRepositoryInterface;
use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestTransitionEntity;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\Workflow\Workflow;

class DebtorInformationChangeRequestManualApprover implements LoggingInterface
{
    use LoggingTrait;

    private $workflow;

    private $changeRequestRepository;

    public function __construct(
        Workflow $debtorInformationChangeRequestWorkflow,
        DebtorInformationChangeRequestRepositoryInterface $changeRequestRepository
    ) {
        $this->workflow = $debtorInformationChangeRequestWorkflow;
        $this->changeRequestRepository = $changeRequestRepository;
    }

    public function approve(DebtorInformationChangeRequestEntity $changeRequest): void
    {
        //TODO: implement the queue logic APIS-1979

        $this->workflow->apply($changeRequest, DebtorInformationChangeRequestTransitionEntity::TRANSITION_REQUEST_CONFIRMATION);
        $this->changeRequestRepository->update($changeRequest);

        $this->logInfo('Debtor information change request {id} sent for manual approval', ['id' => $changeRequest->getId()]);
    }
}
