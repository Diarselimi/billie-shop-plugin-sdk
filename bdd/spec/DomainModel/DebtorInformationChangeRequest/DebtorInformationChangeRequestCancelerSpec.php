<?php

namespace spec\App\DomainModel\DebtorInformationChangeRequest;

use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestCanceledAnnouncer;
use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestCanceler;
use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestEntity;
use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestRepositoryInterface;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Workflow\Workflow;

class DebtorInformationChangeRequestCancelerSpec extends ObjectBehavior
{
    private const COMPANY_UUID = 'company_uuid';

    public function let(
        Workflow $debtorInformationChangeRequestWorkflow,
        DebtorInformationChangeRequestRepositoryInterface $repository,
        DebtorInformationChangeRequestCanceledAnnouncer $announcer
    ) {
        $this->beConstructedWith($debtorInformationChangeRequestWorkflow, $repository, $announcer);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(DebtorInformationChangeRequestCanceler::class);
    }

    public function it_should_announce_change_request_canceled(
        DebtorInformationChangeRequestRepositoryInterface $repository,
        DebtorInformationChangeRequestCanceledAnnouncer $announcer
    ) {
        // Arrange
        $existingChangeRequest = new DebtorInformationChangeRequestEntity();
        $repository
            ->getPendingByCompanyUuid(self::COMPANY_UUID)
            ->shouldBeCalledOnce()
            ->willReturn($existingChangeRequest);
        $repository->update($existingChangeRequest)->shouldBeCalledOnce();

        // Assert
        $announcer
            ->announceChangeRequestCanceled($existingChangeRequest)
            ->shouldBeCalledOnce();

        // Act
        $merchantDebtor = (new MerchantDebtorEntity())
            ->setCompanyUuid(self::COMPANY_UUID);
        $this->cancel(
            $merchantDebtor
        );
    }
}
