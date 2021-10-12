<?php

namespace App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestApprover;

use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestCreatedAnnouncer;
use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestEntity;
use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestRepositoryInterface;
use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestTransitionEntity;
use App\DomainModel\Merchant\MerchantRepository;
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

    private $merchantRepository;

    public function __construct(
        Workflow $debtorInformationChangeRequestWorkflow,
        DebtorInformationChangeRequestRepositoryInterface $changeRequestRepository,
        DebtorInformationChangeRequestCreatedAnnouncer $debtorInformationChangeRequestAnnouncer,
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantRepository $merchantRepository
    ) {
        $this->workflow = $debtorInformationChangeRequestWorkflow;
        $this->changeRequestRepository = $changeRequestRepository;
        $this->debtorInformationChangeRequestAnnouncer = $debtorInformationChangeRequestAnnouncer;
        $this->merchantUserRepository = $merchantUserRepository;
        $this->merchantRepository = $merchantRepository;
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
        $merchant = $this->merchantRepository->getOneById($merchantUser->getMerchantId());
        $this->debtorInformationChangeRequestAnnouncer->announceChangeRequestCreated(
            $changeRequestEntity,
            $merchant->getCompanyUuid(),
            $merchantUser->getUuid()
        );

        $this->logInfo(
            'Debtor information change request {id} sent for manual approval',
            [LoggingInterface::KEY_ID => $changeRequestEntity->getId()]
        );
    }
}
