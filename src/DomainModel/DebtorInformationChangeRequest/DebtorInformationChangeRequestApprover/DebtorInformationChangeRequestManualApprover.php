<?php

namespace App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestApprover;

use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestCreatedAnnouncer;
use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestEntity;
use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestRepositoryInterface;
use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestTransitionEntity;
use App\DomainModel\MerchantUser\MerchantUserRepositoryInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\Workflow\Workflow;

class DebtorInformationChangeRequestManualApprover implements LoggingInterface
{
    use LoggingTrait;

    private $workflow;

    private $changeRequestRepository;

    private $debtorInformationChangeRequestAnnouncer;

    private $merchantUserRepository;

    public function __construct(
        Workflow $debtorInformationChangeRequestWorkflow,
        DebtorInformationChangeRequestRepositoryInterface $changeRequestRepository,
        DebtorInformationChangeRequestCreatedAnnouncer $debtorInformationChangeRequestAnnouncer,
        MerchantUserRepositoryInterface $merchantUserRepository
    ) {
        $this->workflow = $debtorInformationChangeRequestWorkflow;
        $this->changeRequestRepository = $changeRequestRepository;
        $this->debtorInformationChangeRequestAnnouncer = $debtorInformationChangeRequestAnnouncer;
        $this->merchantUserRepository = $merchantUserRepository;
    }

    public function approve(DebtorInformationChangeRequestEntity $changeRequestEntity): void
    {
        $this->workflow->apply(
            $changeRequestEntity,
            DebtorInformationChangeRequestTransitionEntity::TRANSITION_REQUEST_CONFIRMATION
        );
        $this->changeRequestRepository->update($changeRequestEntity);

        $merchantUser = $this->merchantUserRepository->getOneById(
            $changeRequestEntity->getMerchantUserId()
        );
        $this->debtorInformationChangeRequestAnnouncer->announceChangeRequestCreated(
            $changeRequestEntity,
            $merchantUser->getUuid()
        );

        $this->logInfo(
            'Debtor information change request {id} sent for manual approval',
            ['id' => $changeRequestEntity->getId()]
        );
    }
}
