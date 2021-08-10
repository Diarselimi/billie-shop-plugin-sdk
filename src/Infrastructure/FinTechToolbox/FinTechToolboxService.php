<?php

declare(strict_types=1);

namespace App\Infrastructure\FinTechToolbox;

use App\DomainModel\BankAccount\BicLookupServiceInterface;
use App\DomainModel\BankAccount\BicLookupServiceRequestException;
use App\DomainModel\BankAccount\BicNotFoundException;
use App\DomainModel\BankAccount\IbanDTO;
use App\Infrastructure\Banco\BancoSdkWrapper;
use App\Infrastructure\DecodeResponseTrait;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\TransferStats;

/**
 * @deprecated
 * @see BancoSdkWrapper
 */
class FinTechToolboxService implements BicLookupServiceInterface, LoggingInterface
{
    use LoggingTrait, DecodeResponseTrait;

    private $client;

    public function __construct(Client $bicLookupClient)
    {
        $this->client = $bicLookupClient;
    }

    public function lookup(IbanDTO $iban): FinTechToolboxResponseDTO
    {
        try {
            $response = $this->client->get('bankcodes/' . $iban->getBankCode() . '.json', [
                'on_stats' => function (TransferStats $stats) {
                    $this->logServiceRequestStats($stats, 'bic_lookup');
                },
            ]);

            $decodedResponse = $this->decodeResponse($response);
            $decodedResponse = $decodedResponse['bank_code'] ?? null;

            if (!$decodedResponse) {
                throw new BicNotFoundException();
            }

            $bankData = (new FinTechToolboxResponseDTO())
                ->setBankName($decodedResponse['bank_name'] ?? null)
                ->setCode($decodedResponse['code'] ?? null)
                ->setBic($decodedResponse['bic'] ?? null)
                ->setCity($decodedResponse['city'] ?? null)
                ->setPostalCode($decodedResponse['postal_code'] ?? null);

            return $bankData;
        } catch (TransferException $exception) {
            $this->logError('Request to FinTech Toolbox failed: ' . $exception->getMessage());

            throw new BicLookupServiceRequestException($exception);
        }
    }
}
