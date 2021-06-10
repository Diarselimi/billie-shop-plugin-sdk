<?php

namespace App\Http\Controller\PublicApi\Dashboard;

use App\Application\UseCase\VerifyNewDebtorExternalCode\DebtorExternalCodeTakenException;
use App\Application\UseCase\VerifyNewDebtorExternalCode\VerifyNewDebtorExternalCodeRequest;
use App\Application\UseCase\VerifyNewDebtorExternalCode\VerifyNewDebtorExternalCodeUseCase;
use App\Http\HttpConstantsInterface;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

/**
 * @IsGranted("ROLE_CREATE_ORDERS")
 *
 * @OA\Get(
 *     path="/merchant/verify-new-external-code/{externalCode}",
 *     operationId="verify_new_external_code",
 *     summary="Verify new External Code for Merchant",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Dashboard Merchants"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Parameter(in="path", name="externalCode", @OA\Schema(ref="#/components/schemas/TinyText"), description="Merchant debtor external code",required=true),
 *
 *     @OA\Response(response=204, description="Valid new external code"),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=409, ref="#/components/responses/ResourceConflict"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class VerifyNewDebtorExternalCodeController
{
    private $useCase;

    public function __construct(
        VerifyNewDebtorExternalCodeUseCase $useCase
    ) {
        $this->useCase = $useCase;
    }

    public function execute(string $externalCode, Request $request): void
    {
        try {
            $merchantId = $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID);

            $this->useCase->execute((new VerifyNewDebtorExternalCodeRequest($merchantId, $externalCode)));
        } catch (DebtorExternalCodeTakenException $exception) {
            throw new ConflictHttpException('Debtor external code already taken', $exception);
        }
    }
}
