<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApi;

use App\Application\Exception\WorkflowException;
use App\Application\UseCase\MerchantFinishIntegration\MerchantFinishIntegrationRequest;
use App\Application\UseCase\MerchantFinishIntegration\MerchantFinishIntegrationUseCase;
use App\DomainModel\MerchantOnboarding\MerchantOnboardingStepNotFoundException;
use App\Http\Authentication\UserProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @IsGranted("ROLE_MANAGE_ONBOARDING")
 *
 * @OA\Post(
 *     path="/merchant/finish-integration",
 *     operationId="merchant_finish_integration",
 *     summary="Finish Technical Integration",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Dashboard Merchants"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Response(response=204, description="Step successfully updated."),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class MerchantFinishIntegrationController
{
    private $useCase;

    private $userProvider;

    public function __construct(
        MerchantFinishIntegrationUseCase $useCase,
        UserProvider $userProvider
    ) {
        $this->useCase = $useCase;
        $this->userProvider = $userProvider;
    }

    public function execute()
    {
        $useCaseRequest = new MerchantFinishIntegrationRequest(
            $this->userProvider->getUser()->getMerchant()->getId()
        );

        try {
            $this->useCase->execute($useCaseRequest);
        } catch (WorkflowException | MerchantOnboardingStepNotFoundException $exception) {
            throw new BadRequestHttpException('Not acceptable request.');
        }
    }
}
