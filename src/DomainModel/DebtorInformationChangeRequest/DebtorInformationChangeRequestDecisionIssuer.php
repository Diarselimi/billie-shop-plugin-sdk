<?php

namespace App\DomainModel\DebtorInformationChangeRequest;

use App\Application\Exception\WorkflowException;
use App\DomainEvent\DebtorInformationChangeRequest\DebtorInformationChangeRequestCompletedEvent;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceRequestException;
use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestApprover\DebtorInformationChangeRequestApproverException;
use App\DomainModel\DebtorInformationChangeRequest\Exception\ChangeRequestNotFoundException;
use App\DomainModel\DebtorInformationChangeRequest\Exception\InvalidDecisionValueException;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Transfer\Message\CompanyInformationChangeRequest\CompanyInformationChangeRequestDecisionIssued;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Workflow\Workflow;

class DebtorInformationChangeRequestDecisionIssuer implements LoggingInterface
{
    use LoggingTrait;

    private const CHANGE_REASON = 'merchant_request';

    private const DECISION_APPROVED = 'approved';

    private const DECISION_DECLINED = 'declined';

    private $workflow;

    private $repository;

    private $eventDispatcher;

    private $companiesService;

    public function __construct(
        Workflow $debtorInformationChangeRequestWorkflow,
        DebtorInformationChangeRequestRepositoryInterface $repository,
        EventDispatcherInterface $eventDispatcher,
        CompaniesServiceInterface $companiesService
    ) {
        $this->workflow = $debtorInformationChangeRequestWorkflow;
        $this->repository = $repository;
        $this->eventDispatcher = $eventDispatcher;
        $this->companiesService = $companiesService;
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
            case self::DECISION_APPROVED:
                $transitionName = DebtorInformationChangeRequestTransitionEntity::TRANSITION_COMPLETE_MANUALLY;

                break;
            case self::DECISION_DECLINED:
                $transitionName = DebtorInformationChangeRequestTransitionEntity::TRANSITION_DECLINE_MANUALLY;

                break;
            default:
                throw new InvalidDecisionValueException(sprintf(
                    'Invalid decision value "%s" for change request with uuid %s',
                    $message->getDecision(),
                    $message->getRequestUuid()
                ));
        }

        if (!$this->workflow->can($changeRequest, $transitionName)) {
            throw new WorkflowException(sprintf(
                'Cannot issue decision (transition: %s). Change request is in %s state.',
                $transitionName,
                $changeRequest->getState()
            ));
        }

        if ($message->getDecision() === self::DECISION_APPROVED) {
            try {
                $this->companiesService->updateCompany($changeRequest->getCompanyUuid(), [
                    'change_reason_uuid' => $changeRequest->getUuid(),
                    'name' => $changeRequest->getName(),
                    'address_street' => $changeRequest->getStreet(),
                    'address_house' => $changeRequest->getHouseNumber(),
                    'address_city' => $changeRequest->getCity(),
                    'address_postal_code' => $changeRequest->getPostalCode(),
                    'change_reason' => self::CHANGE_REASON,
                ]);

                $this->dispatchChangeRequestEvent($changeRequest, $transitionName);
            } catch (CompaniesServiceRequestException $exception) {
                throw new DebtorInformationChangeRequestApproverException(
                    'Manual change request approval failed',
                    null,
                    $exception
                );
            }
        }

        $this->workflow->apply($changeRequest, $transitionName);
        $this->repository->update($changeRequest);

        $this->logInfo(
            'Debtor information change request {id} decision issued',
            ['id' => $changeRequest->getId()]
        );
    }

    private function dispatchChangeRequestEvent(DebtorInformationChangeRequestEntity $changeRequest, string $transitionName): void
    {
        if ($transitionName === DebtorInformationChangeRequestTransitionEntity::TRANSITION_COMPLETE_MANUALLY) {
            $this->eventDispatcher->dispatch(new DebtorInformationChangeRequestCompletedEvent($changeRequest));
        }
    }
}
