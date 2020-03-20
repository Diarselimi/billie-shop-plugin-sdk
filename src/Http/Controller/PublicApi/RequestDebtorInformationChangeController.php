<?php

namespace App\Http\Controller\PublicApi;

use App\Application\Exception\CompanyNotFoundException;
use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\RequestDebtorInformationChange\RequestDebtorInformationChangeRequest;
use App\Application\UseCase\RequestDebtorInformationChange\RequestDebtorInformationChangeUseCase;
use App\Http\Authentication\UserProvider;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @IsGranted("ROLE_CHANGE_DEBTOR_INFORMATION")
 *
 * @OA\Post(
 *     path="/debtor/{uuid}/information-change-request",
 *     operationId="debtor_information_change_request",
 *     summary="Request Debtor Information Change",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Dashboard Debtors"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Parameter(in="path", name="uuid", @OA\Schema(ref="#/components/schemas/UUID"), description="Merchant debtor UUID",required=true),
 *
 *     @OA\Response(response=201, @OA\JsonContent(ref="#/components/schemas/JsonResponse"), description="Debtor information change request created"),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class RequestDebtorInformationChangeController
{
    private $useCase;

    private $userProvider;

    public function __construct(
        RequestDebtorInformationChangeUseCase $useCase,
        UserProvider $userProvider
    ) {
        $this->useCase = $useCase;
        $this->userProvider = $userProvider;
    }

    public function execute(string $uuid, Request $request): JsonResponse
    {
        try {
            $user = $this->userProvider->getMerchantUser()->getUserEntity();
            $changeRequest = (new RequestDebtorInformationChangeRequest())
                ->setDebtorUuid($uuid)
                ->setMerchantUserId($user->getId())
                ->setName($request->request->get('name'))
                ->setCity($request->request->get('address_city'))
                ->setPostalCode($request->request->get('address_postal_code'))
                ->setStreet($request->request->get('address_street'))
                ->setHouseNumber($request->request->get('address_house'))
                ->setTcAccepted($request->request->getBoolean('tc_accepted'))
            ;

            $this->useCase->execute($changeRequest);

            return new JsonResponse(null, JsonResponse::HTTP_CREATED);
        } catch (MerchantDebtorNotFoundException $exception) {
            throw new NotFoundHttpException('Merchant Debtor not found.', $exception);
        } catch (CompanyNotFoundException $exception) {
            throw new NotFoundHttpException('Company not found', $exception);
        }
    }
}
