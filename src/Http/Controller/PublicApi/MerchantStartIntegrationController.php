<?php

namespace App\Http\Controller\PublicApi;

use App\Application\UseCase\MerchantStartIntegration\MerchantStartIntegrationException;
use App\Application\UseCase\MerchantStartIntegration\MerchantStartIntegrationNotAllowedException;
use App\Application\UseCase\MerchantStartIntegration\MerchantStartIntegrationRequest;
use App\Application\UseCase\MerchantStartIntegration\MerchantStartIntegrationResponse;
use App\Application\UseCase\MerchantStartIntegration\MerchantStartIntegrationUseCase;
use App\Http\Authentication\UserProvider;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @IsGranted({"ROLE_MANAGE_ONBOARDING"})
 * @OA\Post(
 *     path="/merchant/start-integration",
 *     operationId="merchant_start_integration",
 *     summary="Start Merchant Integration",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Dashboard Merchants"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Response(
 *          response=200,
 *          description="Successful response",
 *          @OA\JsonContent(ref="#/components/schemas/MerchantStartIntegrationResponse")
 *     ),
 *
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=403, ref="#/components/responses/Forbidden"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class MerchantStartIntegrationController
{
    private $useCase;

    public function __construct(MerchantStartIntegrationUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(UserProvider $userProvider): MerchantStartIntegrationResponse
    {
        try {
            $useCaseRequest = new MerchantStartIntegrationRequest($userProvider->getUser()->getMerchant()->getId());

            return $this->useCase->execute($useCaseRequest);
        } catch (MerchantStartIntegrationNotAllowedException $exception) {
            throw new AccessDeniedHttpException('Start integration not allowed', $exception);
        } catch (MerchantStartIntegrationException $exception) {
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, 'Integration cannot be started', $exception);
        }
    }
}
