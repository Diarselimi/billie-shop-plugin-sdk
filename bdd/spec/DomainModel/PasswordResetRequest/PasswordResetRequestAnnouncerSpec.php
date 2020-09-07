<?php

namespace spec\App\DomainModel\PasswordResetRequest;

use Ozean12\Transfer\Message\MerchantUser\MerchantUserNewPasswordRequested;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class PasswordResetRequestAnnouncerSpec extends ObjectBehavior
{
    public function let(MessageBusInterface $bus, LoggerInterface $logger): void
    {
        $this->beConstructedWith(...func_get_args());
        $this->setLogger($logger);
    }

    public function it_should_dispatch_message(MessageBusInterface $bus): void
    {
        $merchantPaymentUuid = Uuid::uuid4()->toString();
        $token = 'someToken';
        $email = 'test@billie.dev';
        $firstName = 'Roel';
        $lastName = 'Philipsen';
        $bus->dispatch(Argument::that(
            function (MerchantUserNewPasswordRequested $message) use (
                $merchantPaymentUuid,
                $token,
                $email,
                $firstName,
                $lastName
            ) {
                return $message->getMerchantPaymentUuid() === $merchantPaymentUuid
                    && $message->getToken() === $token
                    && $message->getEmail() === $email
                    && $message->getFirstName() === $firstName
                    && $message->getLastName() === $lastName;
            }
        ))->shouldBeCalledOnce()->willReturn(new Envelope(new MerchantUserNewPasswordRequested()));

        $this->announcePasswordResetRequested(
            $merchantPaymentUuid,
            $token,
            $email,
            $firstName,
            $lastName
        );
    }
}
