<?php

namespace App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestApprover;

use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceRequestException;
use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestEntity;
use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestRepositoryInterface;
use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestTransitionEntity;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\Workflow\Workflow;

class DebtorInformationChangeRequestAutomaticApprover implements LoggingInterface
{
    use LoggingTrait;

    private const CHANGE_REASON = 'merchant_update';

    private $companiesService;

    private $workflow;

    private $changeRequestRepository;

    public function __construct(
        CompaniesServiceInterface $companiesService,
        Workflow $debtorInformationChangeRequestWorkflow,
        DebtorInformationChangeRequestRepositoryInterface $changeRequestRepository
    ) {
        $this->companiesService = $companiesService;
        $this->workflow = $debtorInformationChangeRequestWorkflow;
        $this->changeRequestRepository = $changeRequestRepository;
    }

    public function approve(DebtorInformationChangeRequestEntity $changeRequest): void
    {
        $debtorUpdateRequest = array_combine(
            ['change_reason_uuid', 'name', 'address_street', 'address_house', 'address_city', 'address_postal_code'],
            $changeRequest->toArray(['uuid', 'name', 'street', 'house_number', 'city', 'postal_code'])
        );

        $debtorUpdateRequest['change_reason'] = self::CHANGE_REASON;

        try {
            $this->companiesService->updateCompany($changeRequest->getCompanyUuid(), $debtorUpdateRequest);
        } catch (CompaniesServiceRequestException $exception) {
            throw new DebtorInformationChangeRequestApproverException(
                'Automatic change request approval failed',
                null,
                $exception
            );
        }

        $this->workflow->apply($changeRequest, DebtorInformationChangeRequestTransitionEntity::TRANSITION_COMPLETE_AUTOMATICALLY);
        $this->changeRequestRepository->update($changeRequest);

        $this->logInfo('Debtor information change request {id} approved automatically', ['id' => $changeRequest->getId()]);
    }
}
