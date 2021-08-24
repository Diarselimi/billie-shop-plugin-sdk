<?php

namespace App\DomainModel\Mandate;

use App\DomainModel\BankAccount\BankAccountServiceException;
use App\DomainModel\BankAccount\BankAccountServiceInterface;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Sepa\Client\DomainModel\Mandate\GenerateMandateRequest;
use Ozean12\Sepa\Client\DomainModel\Mandate\SepaMandate;
use Ozean12\Sepa\Client\DomainModel\SepaClientInterface;
use Ozean12\Support\HttpClient\Exception\HttpClientExceptionInterface;
use Ozean12\Support\ValueObject\Address;
use Ozean12\Support\ValueObject\BankAccount;
use Ozean12\Support\ValueObject\Iban;
use Ramsey\Uuid\Uuid;

class SepaMandateGenerator implements LoggingInterface
{
    use LoggingTrait;

    private const CREDITOR_COMPANY_UUID = 'a015eaed-8100-4c64-9913-f7cdd8621faf';

    private SepaClientInterface $sepaClient;

    private BankAccountServiceInterface $bankAccountService;

    public function __construct(SepaClientInterface $sepaClient, BankAccountServiceInterface $bankAccountService)
    {
        $this->sepaClient = $sepaClient;
        $this->bankAccountService = $bankAccountService;
    }

    public function generateForOrderContainer(OrderContainer $orderContainer, Iban $iban, string $bankAccountOwner): SepaMandate
    {
        $debtorAddress = $orderContainer->getDebtorCompany()->getAddress();
        $address = new Address(
            $debtorAddress->getCountry(),
            $debtorAddress->getCity(),
            $debtorAddress->getPostalCode(),
            $debtorAddress->getStreet(),
            $debtorAddress->getHouseNumber(),
            $debtorAddress->getAddition()
        );

        try {
            $bank = $this->bankAccountService->getBankByIban($iban);
        } catch (BankAccountServiceException $exception) {
            $this->logSuppressedException($exception, $exception->getMessage());

            throw new GenerateMandateException($exception);
        }

        $bankAccount = new BankAccount($iban, $bank->getBic(), $bank->getName(), $bankAccountOwner);
        $bankAccountUuid = Uuid::uuid4(); // awaits clarifications from Payments team

        $request = new GenerateMandateRequest(
            $address,
            $bankAccount,
            SepaMandate::SCHEME_CORE,
            $bankAccountUuid,
            Uuid::fromString(self::CREDITOR_COMPANY_UUID),
            Uuid::fromString($orderContainer->getOrder()->getUuid())
        );

        try {
            return $this->sepaClient->generateMandate($request);
        } catch (HttpClientExceptionInterface $exception) {
            $this->logSuppressedException($exception, $exception->getMessage());

            throw new GenerateMandateException($exception);
        }
    }
}
