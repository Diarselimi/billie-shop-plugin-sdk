<?php

declare(strict_types=1);

namespace App\Infrastructure\ProdAccess;

use App\Application\UseCase\AuthorizeSandbox\AuthorizeSandboxDTO;
use App\DomainModel\MerchantUser\MerchantUserEntityFactory;
use App\DomainModel\Sandbox\ProdAccessClientInterface;
use App\DomainModel\Sandbox\ProdAccessRequestException;
use App\DomainModel\Sandbox\SandboxClientNotAvailableException;
use App\Infrastructure\DecodeResponseTrait;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ProdAccessClient implements ProdAccessClientInterface
{
    use LoggingTrait, DecodeResponseTrait;

    private $paellaProdClient;

    private $merchantUserEntityFactory;

    public function __construct(
        Client $paellaProdClient,
        MerchantUserEntityFactory $factory
    ) {
        if (!$paellaProdClient->getConfig('base_uri')) {
            throw new SandboxClientNotAvailableException();
        }
        $this->paellaProdClient = $paellaProdClient;
        $this->merchantUserEntityFactory = $factory;
    }

    public function authorizeTokenForSandbox(string $token): AuthorizeSandboxDTO
    {
        try {
            $response = $this->paellaProdClient->post("api/authorize-sandbox", [
                'headers' => ['Authorization' => $token],
            ]);

            $response = $this->decodeResponse($response);

            if ($response['user_entity']['permissions']) {
                $response['user_entity']['permissions'] = json_encode($response['user_entity']['permissions']);
            }

            return new AuthorizeSandboxDTO(
                $this->merchantUserEntityFactory->createFromDatabaseRow($response['user_entity']),
                $response['email'],
                $response['sandbox_payment_merchant_uuid']
            );
        } catch (TransferException | AccessDeniedHttpException $exception) {
            throw new ProdAccessRequestException($exception);
        }
    }
}
