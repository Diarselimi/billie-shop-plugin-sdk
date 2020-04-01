<?php

namespace App\Http\Controller\PublicApi;

use App\Application\UseCase\GetMerchantSepaMandate\GetMerchantSepaMandateRequest;
use App\Application\UseCase\GetMerchantSepaMandate\GetMerchantSepaMandateUseCase;
use App\Http\Authentication\UserProvider;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @IsGranted({"ROLE_VIEW_ONBOARDING"})
 *
 * @OA\Get(
 *     path="/merchant/bank-account/sepa-mandate-document",
 *     operationId="get_sepa_b2b_document",
 *     summary="Get Sepa B2B Mandate",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Dashboard Merchants"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Response(response=200, description="The SEPA mandate PDF file contents",
 *          content={@OA\MediaType(mediaType="application/pdf", @OA\Schema(type="string", format="binary"))}
 *      ),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=403, ref="#/components/responses/Forbidden"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class GetMerchantSepaMandateController
{
    private $useCase;

    private $userProvider;

    public function __construct(GetMerchantSepaMandateUseCase $useCase, UserProvider $userProvider)
    {
        $this->useCase = $useCase;
        $this->userProvider = $userProvider;
    }

    public function execute(Request $request): StreamedResponse
    {
        $merchant = $this->userProvider->getUser()->getMerchant();
        $useCaseRequest = new GetMerchantSepaMandateRequest($merchant->getSepaB2BDocumentUuid());

        $response = $this->useCase->execute($useCaseRequest);

        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'sepa_b2b_document.pdf'
        );
        $response->headers->set('Content-Disposition', $disposition);
        $response->headers->set('Content-Type', 'application/pdf');

        $response->prepare($request);

        return $response;
    }
}
