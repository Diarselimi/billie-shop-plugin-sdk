<?php

declare(strict_types=1);

namespace App\DomainModel\BankAccount;

use App\Support\DateFormat;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Transfer\Message\BankAccount\BankAccountCoreAccepted;
use Ozean12\Transfer\Shared\BankAccount;
use Symfony\Component\Messenger\MessageBusInterface;

class BankAccountCoreAcceptedAnnouncer implements LoggingInterface
{
    use LoggingTrait;

    private $bus;

    public function __construct(MessageBusInterface $bus)
    {
        $this->bus = $bus;
    }

    public function announce(BankAccountDTO $bankAccountDTO, string $mandateReference)
    {
        $bankAccount = (new BankAccount())
            ->setUuid($bankAccountDTO->getUuid())
            ->setName($bankAccountDTO->getName())
            ->setIban($bankAccountDTO->getIban()->getIban())
            ->setBic($bankAccountDTO->getBic())
            ->setMerchantUuid($bankAccountDTO->getPaymentUuid());

        $message = (new BankAccountCoreAccepted())
            ->setBankAccount($bankAccount)
            ->setMandateReference($mandateReference)
            ->setMandateValidFrom((new \DateTime())->format(DateFormat::FORMAT_YMD_HIS));

        $this->bus->dispatch($message);
        $this->logInfo('BankAccountCoreAccepted event announced');
    }
}
