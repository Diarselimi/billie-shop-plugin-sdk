<?php

declare(strict_types=1);

namespace App\DomainModel\BankAccount;

use App\Helper\Uuid\UuidGeneratorInterface;
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

    private $mandateReferenceGenerator;

    private $uuidGenerator;

    public function __construct(
        MessageBusInterface $bus,
        SepaMandateReferenceGenerator $mandateReferenceGenerator,
        UuidGeneratorInterface $uuidGenerator
    ) {
        $this->bus = $bus;
        $this->mandateReferenceGenerator = $mandateReferenceGenerator;
        $this->uuidGenerator = $uuidGenerator;
    }

    public function announce(BankAccountDTO $bankAccountDTO)
    {
        $bankAccount = (new BankAccount())
            ->setUuid($bankAccountDTO->getUuid())
            ->setName($bankAccountDTO->getName())
            ->setIban($bankAccountDTO->getIban()->getIban())
            ->setBic($bankAccountDTO->getBic())
            ->setMerchantUuid($bankAccountDTO->getPaymentUuid());

        $message = (new BankAccountCoreAccepted())
            ->setBankAccount($bankAccount)
            ->setMandateReference($this->mandateReferenceGenerator->generate())
            ->setMandateValidFrom((new \DateTime())->format(DateFormat::FORMAT_YMD_HIS));

        $this->bus->dispatch($message);
        $this->logInfo('BankAccountCoreAccepted event announced');
    }
}
