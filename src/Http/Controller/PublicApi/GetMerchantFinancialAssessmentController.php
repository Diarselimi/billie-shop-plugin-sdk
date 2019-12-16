<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApi;

use App\Application\UseCase\GetMerchantFinancialAssessment\FinancialAssessmentNotFoundException;
use App\Application\UseCase\GetMerchantFinancialAssessment\GetMerchantFinancialAssessmentRequest;
use App\Application\UseCase\GetMerchantFinancialAssessment\GetMerchantFinancialAssessmentResponse;
use App\Application\UseCase\GetMerchantFinancialAssessment\GetMerchantFinancialAssessmentUseCase;
use App\Http\Authentication\UserProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @IsGranted("ROLE_MANAGE_ONBOARDING")
 *
 * @OA\Get(
 *     path="/merchant/financial-assessment",
 *     operationId="get_merchant_financial_assessment",
 *     summary="Get Merchant Financial Assessment",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Dashboard Merchants"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Response(response=200, description="Merchant financial assessment", @OA\JsonContent(ref="#/components/schemas/GetMerchantFinancialAssessmentResponse")),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class GetMerchantFinancialAssessmentController
{
    private $useCase;

    private $userProvider;

    public function __construct(
        GetMerchantFinancialAssessmentUseCase $useCase,
        UserProvider $userProvider
    ) {
        $this->useCase = $useCase;
        $this->userProvider = $userProvider;
    }

    public function execute(): GetMerchantFinancialAssessmentResponse
    {
        $useCaseRequest = new GetMerchantFinancialAssessmentRequest(
            $this->userProvider->getUser()->getMerchant()->getId()
        );

        try {
            return $this->useCase->execute($useCaseRequest);
        } catch (FinancialAssessmentNotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        }
    }
}
