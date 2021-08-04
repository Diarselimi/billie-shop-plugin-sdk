<?php

declare(strict_types=1);

namespace App\Infrastructure\Banco;

use App\DomainModel\BankAccount\BankAccountServiceException;
use App\DomainModel\BankAccount\BankAccountServiceInterface;
use App\DomainModel\BankAccount\BankNotFoundException;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\BancoSDK\Api\BankApi;
use Ozean12\BancoSDK\ApiException;
use Ozean12\BancoSDK\Model\Bank;

class BancoSdkWrapper implements BankAccountServiceInterface, LoggingInterface
{
    use LoggingTrait;

    private BankApi $bancoApi;

    public function __construct(BankApi $bancoApi)
    {
        $this->bancoApi = $bancoApi;
    }

    public function getBankByBic(string $bic): Bank
    {
        $bic = strtoupper($bic);

        try {
            $response = $this->bancoApi->searchBanks($bic, 3);
        } catch (ApiException $exception) {
            $this->logSuppressedException($exception);

            throw new BankAccountServiceException();
        }

        $banks = $response->getBanks();

        if ($banks === null || empty($banks)) {
            throw new BankNotFoundException();
        }

        foreach ($banks as $bank) {
            $resultBic = strtoupper($bank->getBic());
            if (($resultBic === $bic) || ($resultBic === ($bic . 'XXX'))) {
                return $bank;
            }
        }

        throw new BankNotFoundException();
    }
}
