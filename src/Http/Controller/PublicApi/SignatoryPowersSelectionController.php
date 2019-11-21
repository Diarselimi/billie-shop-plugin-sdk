<?php

namespace App\Http\Controller\PublicApi;

use App\Application\UseCase\SignatoryPowersSelection\SignatoryPowersSelectionException;
use App\Application\UseCase\SignatoryPowersSelection\SignatoryPowersSelectionRequest;
use App\Application\UseCase\SignatoryPowersSelection\SignatoryPowersSelectionUseCase;
use App\DomainModel\SignatoryPowersSelection\SignatoryPowerDTO;
use App\Http\Authentication\UserProvider;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @IsGranted("ROLE_MANAGE_ONBOARDING")
 *
 * @OA\Post(
 *     path="/merchant/signatory-powers-selection",
 *     operationId="signatory_powers_selection",
 *     summary="Signatory powers selection",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Dashboard Merchant"},
 *     x={"groups":{"private"}},
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(ref="#/components/schemas/SignatoryPowersSelectionRequest"))
 *     ),
 *
 *     @OA\Response(response=204, description="Signatory powers saved successfully."),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class SignatoryPowersSelectionController
{
    private $useCase;

    private $userProvider;

    public function __construct(SignatoryPowersSelectionUseCase $useCase, UserProvider $userProvider)
    {
        $this->useCase = $useCase;
        $this->userProvider = $userProvider;
    }

    public function execute(Request $request): void
    {
        $requestDTOs = array_map(function (array $data) {
            return (new SignatoryPowerDTO())
                ->setUuid($data['uuid'] ?? null)
                ->setEmail($data['email'] ?? null)
                ->setIsIdentifiedAsUser($data['is_identified_as_user'] ?? false);
        }, $request->request->get('signatory_powers', []));

        $companyId = $this->userProvider->getMerchantUser()->getMerchant()->getCompanyId();

        try {
            $this->useCase->execute(new SignatoryPowersSelectionRequest($companyId, ...$requestDTOs));
        } catch (SignatoryPowersSelectionException $exception) {
            throw new BadRequestHttpException();
        }
    }
}
