<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApi\Dashboard;

use App\Application\UseCase\GetMerchantCredentials\GetMerchantCredentialsRequest;
use App\Application\UseCase\GetMerchantCredentials\GetMerchantCredentialsResponse;
use App\Application\UseCase\GetMerchantCredentials\GetMerchantCredentialsUseCase;
use App\Http\Authentication\UserProvider;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @IsGranted("ROLE_VIEW_CREDENTIALS")
 *
 * @OA\Get(
 *     path="/merchant/credentials",
 *     operationId="get_merchant_credentials",
 *     description="Get Credentials",
 *     summary="Returns merchant credentials for production and sandbox.",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Dashboard Merchants"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Response(response=200, description="Response on success", @OA\JsonContent(ref="#/components/schemas/GetMerchantCredentialsResponse")),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class GetMerchantCredentialsController
{
    private $useCase;

    private $userProvider;

    public function __construct(
        GetMerchantCredentialsUseCase $useCase,
        UserProvider $userProvider
    ) {
        $this->useCase = $useCase;
        $this->userProvider = $userProvider;
    }

    public function execute(): GetMerchantCredentialsResponse
    {
        $merchant = $this->userProvider->getUser()->getMerchant();
        if ($merchant->getOauthClientId() === null) {
            throw new HttpException(500, 'OAuth client not found.');
        }

        $useCaseRequest = new GetMerchantCredentialsRequest(
            $merchant->getId(),
            $merchant->getOauthClientId(),
            $merchant->getSandboxPaymentUuid()
        );

        return $this->useCase->execute($useCaseRequest);
    }
}
