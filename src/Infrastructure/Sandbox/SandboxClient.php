<?php

namespace App\Infrastructure\Sandbox;

use App\DomainModel\Merchant\MerchantEntityFactory;
use App\DomainModel\Merchant\MerchantWithCompanyCreationDTO;
use App\DomainModel\MerchantUser\GetMerchantCredentialsDTO;
use App\DomainModel\Sandbox\SandboxClientInterface;
use App\DomainModel\Sandbox\SandboxClientNotAvailableException;
use App\DomainModel\Sandbox\SandboxMerchantDTO;
use App\DomainModel\Sandbox\SandboxServiceRequestException;
use App\Infrastructure\ClientResponseDecodeException;
use App\Infrastructure\DecodeResponseTrait;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;

class SandboxClient implements SandboxClientInterface
{
    use LoggingTrait, DecodeResponseTrait;

    private $paellaSandboxClient;

    private $merchantEntityFactory;

    public function __construct(Client $paellaSandboxClient, MerchantEntityFactory $merchantEntityFactory)
    {
        if (!$paellaSandboxClient->getConfig('base_uri')) {
            throw new SandboxClientNotAvailableException();
        }
        $this->paellaSandboxClient = $paellaSandboxClient;
        $this->merchantEntityFactory = $merchantEntityFactory;
    }

    public function createMerchant(MerchantWithCompanyCreationDTO $creationDTO): SandboxMerchantDTO
    {
        try {
            $response = $this->paellaSandboxClient->post("/api/merchant/with-company", [
                'json' => $creationDTO->toArray(),
            ]);

            $response = $this->decodeResponse($response);

            return new SandboxMerchantDTO(
                $this->merchantEntityFactory->createFromMerchantCreationResponse($response),
                $response['oauth_client_secret']
            );
        } catch (TransferException | ClientResponseDecodeException $exception) {
            throw new SandboxServiceRequestException($exception);
        }
    }

    public function getMerchantCredentials(string $paymentMerchantUuid): GetMerchantCredentialsDTO
    {
        try {
            $response = $this->paellaSandboxClient->get("/api/merchant/{$paymentMerchantUuid}");
            $response = $this->decodeResponse($response);

            $credentials = $response['credentials'];

            return new GetMerchantCredentialsDTO(
                $credentials['client_id'],
                $credentials['secret']
            );
        } catch (TransferException | ClientResponseDecodeException $exception) {
            throw new SandboxServiceRequestException($exception);
        }
    }
}
