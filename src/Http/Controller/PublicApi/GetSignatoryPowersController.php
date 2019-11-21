<?php

namespace App\Http\Controller\PublicApi;

use App\Application\UseCase\GetSignatoryPowers\GetSignatoryPowersRequest;
use App\Application\UseCase\GetSignatoryPowers\GetSignatoryPowersUseCase;
use App\Application\UseCase\GetSignatoryPowers\GetSignatoryPowersUseCaseException;
use App\DomainModel\GetSignatoryPowers\GetSignatoryPowersResponse;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use App\Http\Authentication\UserProvider;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @IsGranted({"ROLE_VIEW_ONBOARDING"})
 *
 * @OA\Get(
 *     path="/merchant/signatory-powers",
 *     operationId="signatory_powers_get",
 *     summary="Get signatory powers",
 *     security={{"oauth2"={}}},
 *     tags={"Dashboard Merchants"},
 *     x={"groups": {"private"}},
 *
 *     @OA\Response(response=200, @OA\JsonContent(ref="#/components/schemas/GetSignatoryPowersResponse"), description="Signatory Powers List"),
 *
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class GetSignatoryPowersController
{
    private $useCase;

    private $userProvider;

    public function __construct(GetSignatoryPowersUseCase $useCase, UserProvider $userProvider)
    {
        $this->useCase = $useCase;
        $this->userProvider = $userProvider;
    }

    public function execute(): GetSignatoryPowersResponse
    {
        $merchantId = $this->userProvider->getUser()->getMerchant()->getId();
        $userId = $this->userProvider->getMerchantUser()->getUserEntity()->getUuid();

        $signatoryPowersRequest = new GetSignatoryPowersRequest($merchantId, $userId);

        try {
            return $this->useCase->execute($signatoryPowersRequest);
        } catch (GetSignatoryPowersUseCaseException $exception) {
            throw new BadRequestHttpException($exception->getMessage());
        }
    }
}
