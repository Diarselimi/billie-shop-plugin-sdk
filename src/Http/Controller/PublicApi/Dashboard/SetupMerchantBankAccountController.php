<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApi\Dashboard;

use App\Application\UseCase\SetupMerchantBankAccount\SetupMerchantBankAccountException;
use App\Application\UseCase\SetupMerchantBankAccount\SetupMerchantBankAccountMissingBicException;
use App\Application\UseCase\SetupMerchantBankAccount\SetupMerchantBankAccountRequest;
use App\Application\UseCase\SetupMerchantBankAccount\SetupMerchantBankAccountUseCase;
use App\Http\Authentication\UserProvider;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * @IsGranted({"ROLE_MANAGE_ONBOARDING"})
 * @OA\Post(
 *     path="/merchant/bank-account",
 *     operationId="setup_merchant_bank_account",
 *     summary="Setup Bank Account",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Dashboard Merchants"},
 *     x={"groups":{"private"}},
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/SetupMerchantBankAccountRequest"))
 *     ),
 *
 *     @OA\Response(response=204, description="Successful response"),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=403, ref="#/components/responses/Forbidden"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class SetupMerchantBankAccountController
{
    private $useCase;

    private $userProvider;

    public function __construct(SetupMerchantBankAccountUseCase $useCase, UserProvider $userProvider)
    {
        $this->useCase = $useCase;
        $this->userProvider = $userProvider;
    }

    public function execute(Request $request): void
    {
        try {
            $useCaseRequest = (new SetupMerchantBankAccountRequest())
                ->setMerchantId($this->userProvider->getUser()->getMerchant()->getId())
                ->setIban($request->get('iban'))
                ->setTcAccepted($request->request->getBoolean('tc_accepted'));

            $this->useCase->execute($useCaseRequest);
        } catch (SetupMerchantBankAccountException $e) {
            throw new AccessDeniedHttpException($e->getMessage());
        } catch (SetupMerchantBankAccountMissingBicException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }
}
