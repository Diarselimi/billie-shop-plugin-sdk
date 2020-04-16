<?php

namespace App\DomainModel\DebtorInformationChangeRequest;

use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use Symfony\Component\Workflow\Workflow;

class DebtorInformationChangeRequestCanceler
{
    private $workflow;

    private $repository;

    private $announcer;

    public function __construct(
        Workflow $debtorInformationChangeRequestWorkflow,
        DebtorInformationChangeRequestRepositoryInterface $repository,
        DebtorInformationChangeRequestCanceledAnnouncer $announcer
    ) {
        $this->workflow = $debtorInformationChangeRequestWorkflow;
        $this->repository = $repository;
        $this->announcer = $announcer;
    }

    public function cancel(MerchantDebtorEntity $merchantDebtor): void
    {
        $existingChangeRequest = $this->repository->getPendingByCompanyUuid(
            $merchantDebtor->getCompanyUuid()
        );
        if (!$existingChangeRequest) {
            return;
        }

        $existingChangeRequest->setIsSeen(true);
        $this->workflow->apply(
            $existingChangeRequest,
            DebtorInformationChangeRequestTransitionEntity::TRANSITION_CANCEL
        );
        $this->repository->update($existingChangeRequest);

        $this->announcer->announceChangeRequestCanceled($existingChangeRequest);
    }
}
