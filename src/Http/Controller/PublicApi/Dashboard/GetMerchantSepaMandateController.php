<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApi\Dashboard;

use App\Application\UseCase\GetMerchantSepaMandate\GetMerchantSepaMandateRequest;
use App\Application\UseCase\GetMerchantSepaMandate\GetMerchantSepaMandateUseCase;
use App\DomainModel\FileService\FileServiceRequestException;
use App\Http\Authentication\UserProvider;
use App\Http\Response\StreamedResponseBuilder;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=403, ref="#/components/responses/Forbidden"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class GetMerchantSepaMandateController
{
    private GetMerchantSepaMandateUseCase $useCase;

    private UserProvider $userProvider;

    private StreamedResponseBuilder $streamedResponseBuilder;

    public function __construct(
        GetMerchantSepaMandateUseCase $useCase,
        UserProvider $userProvider,
        StreamedResponseBuilder $streamedResponseBuilder
    ) {
        $this->useCase = $useCase;
        $this->userProvider = $userProvider;
        $this->streamedResponseBuilder = $streamedResponseBuilder;
    }

    public function execute(Request $request): StreamedResponse
    {
        $merchant = $this->userProvider->getUser()->getMerchant();
        $useCaseRequest = new GetMerchantSepaMandateRequest($merchant->getSepaB2BDocumentUuid());

        try {
            $fileServiceDownloadResponse = $this->useCase->execute($useCaseRequest);
        } catch (FileServiceRequestException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        }

        return $this->streamedResponseBuilder->build(
            $request,
            'sepa_b2b_document.pdf',
            $fileServiceDownloadResponse->getStream(),
            [
                'Content-type' => $fileServiceDownloadResponse->getContentType(),
            ]
        );
    }
}
