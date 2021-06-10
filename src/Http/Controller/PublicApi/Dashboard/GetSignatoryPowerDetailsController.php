<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApi\Dashboard;

use App\Application\UseCase\GetSignatoryPowerDetails\GetSignatoryPowerDetailsRequest;
use App\Application\UseCase\GetSignatoryPowerDetails\GetSignatoryPowerDetailsResponse;
use App\Application\UseCase\GetSignatoryPowerDetails\GetSignatoryPowerDetailsUseCase;
use App\Http\Authentication\UserProvider;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @IsGranted({"ROLE_AUTHENTICATED_AS_SIGNATORY_POWER_TOKEN_USER"})
 * @OA\Get(
 *     path="/merchant/signatory-powers/{token}",
 *     operationId="get_signatory_power_details",
 *     summary="Get Signatory Power Details",
 *
 *     tags={"Dashboard Merchants"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Parameter(in="path", name="token", @OA\Schema(type="string"), required=true),
 *
 *     @OA\Response(
 *          response=200,
 *          description="Successful response",
 *          @OA\JsonContent(ref="#/components/schemas/GetSignatoryPowerDetailsResponse")
 *     ),
 *
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=403, ref="#/components/responses/Forbidden"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class GetSignatoryPowerDetailsController
{
    private $useCase;

    private $userProvider;

    public function __construct(GetSignatoryPowerDetailsUseCase $useCase, UserProvider $userProvider)
    {
        $this->useCase = $useCase;
        $this->userProvider = $userProvider;
    }

    public function execute(): GetSignatoryPowerDetailsResponse
    {
        $user = $this->userProvider->getSignatoryPowerTokenUser();

        $useCaseRequest = (new GetSignatoryPowerDetailsRequest())
            ->setMerchantName($user->getMerchant()->getName())
            ->setSignatoryPowerDTO($user->getSignatoryPowerDTO());

        return $this->useCase->execute($useCaseRequest);
    }
}
