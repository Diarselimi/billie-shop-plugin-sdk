<?php

namespace spec\App\DomainModel\DebtorInformationChangeRequest;

use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestCanceledAnnouncer;
use App\DomainModel\DebtorInformationChangeRequest\DebtorInformationChangeRequestEntity;
use Ozean12\Transfer\Message\CompanyInformationChangeRequest\CompanyInformationChangeRequestCanceled;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class DebtorInformationChangeRequestCanceledAnnouncerSpec extends ObjectBehavior
{
    const REQUEST_UUID = 'request_uuid';

    public function let(
        MessageBusInterface $bus,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith($bus, 'paella-test');
        $this->setLogger($logger);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(DebtorInformationChangeRequestCanceledAnnouncer::class);
    }

    public function it_should_send_message_successfully(
        MessageBusInterface $bus
    ) {
        $message = (new CompanyInformationChangeRequestCanceled())
            ->setRequestUuid(self::REQUEST_UUID);

        $bus->dispatch($message)->shouldBeCalledOnce()->willReturn(new Envelope($message));

        $changeRequest = (new DebtorInformationChangeRequestEntity())
            ->setUuid(self::REQUEST_UUID);
        $this->announceChangeRequestCanceled(
            $changeRequest
        );
    }
}
