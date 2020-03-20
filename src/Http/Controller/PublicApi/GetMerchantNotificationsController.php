<?php

namespace App\Http\Controller\PublicApi;

use App\Application\UseCase\GetMerchantNotifications\GetMerchantNotificationsRequest;
use App\Application\UseCase\GetMerchantNotifications\GetMerchantNotificationsResponse;
use App\Application\UseCase\GetMerchantNotifications\GetMerchantNotificationsUseCase;
use App\Http\Authentication\UserProvider;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @IsGranted("ROLE_AUTHENTICATED_AS_MERCHANT_USER")
 * @OA\Get(
 *     path="/notifications",
 *     operationId="notifications_get",
 *     summary="Get Notifications",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Dashboard Merchant Notifications"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Response(
 *          response=200,
 *          description="Successful response",
 *          @OA\JsonContent(ref="#/components/schemas/GetMerchantNotificationsResponse")
 *     ),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class GetMerchantNotificationsController
{
    private $useCase;

    private $userProvider;

    public function __construct(
        GetMerchantNotificationsUseCase $useCase,
        UserProvider $userProvider
    ) {
        $this->useCase = $useCase;
        $this->userProvider = $userProvider;
    }

    public function execute(): GetMerchantNotificationsResponse
    {
        $useCaseRequest = new GetMerchantNotificationsRequest(
            $this->userProvider->getMerchantUser()->getMerchant()->getId()
        );

        return $this->useCase->execute($useCaseRequest);
    }
}
