<?php

namespace spec\App\DomainModel\DebtorInformationChangeRequest;

use App\Application\Exception\WorkflowException;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestEntity;
use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestRepositoryInterface;
use Ozean12\Transfer\Message\CompanyInformationChangeRequest\CompanyInformationChangeRequestDecisionIssued;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Workflow\Workflow;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DebtorInformationChangeRequestDecisionIssuerSpec extends ObjectBehavior
{
    public function let(
        Workflow $debtorInformationChangeRequestWorkflow,
        DebtorInformationChangeRequestRepositoryInterface $repository,
        EventDispatcherInterface $eventDispatcher,
        CompaniesServiceInterface $companiesService
    ) {
        $this->beConstructedWith(...func_get_args());
    }

    public function it_should_throw_exception_if_workflow_cant_transition(
        DebtorInformationChangeRequestRepositoryInterface $repository,
        Workflow $debtorInformationChangeRequestWorkflow
    ) {
        $uuid = 'dummy-uuid';
        $changeRequest = (new DebtorInformationChangeRequestEntity())
            ->setUuid($uuid);
        $repository
            ->getOneByUuid(Argument::any())
            ->willReturn($changeRequest);
        $debtorInformationChangeRequestWorkflow
            ->can(Argument::any(), Argument::any())
            ->shouldBeCalledOnce()
            ->willReturn(false);

        $message = (new CompanyInformationChangeRequestDecisionIssued())
            ->setDecision('declined');
        $this
            ->shouldThrow(WorkflowException::class)
            ->during('issueDecision', [$message]);
    }
}
