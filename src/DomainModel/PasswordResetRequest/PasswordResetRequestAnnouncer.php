<?php

declare(strict_types=1);

namespace App\DomainModel\PasswordResetRequest;

use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Transfer\Message\MerchantUser\MerchantUserNewPasswordRequested;
use Symfony\Component\Messenger\MessageBusInterface;

class PasswordResetRequestAnnouncer implements LoggingInterface
{
    use LoggingTrait;

    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function announcePasswordResetRequested(
        string $merchantPaymentUuid,
        string $token,
        string $email,
        string $firstName,
        string $lastName
    ): void {
        $message = (new MerchantUserNewPasswordRequested())
            ->setMerchantPaymentUuid($merchantPaymentUuid)
            ->setToken($token)
            ->setEmail($email)
            ->setFirstName($firstName)
            ->setLastName($lastName);

        $this->bus->dispatch($message);
        $this->logInfo('MerchantUserNewPasswordRequested event announced');
    }
}
