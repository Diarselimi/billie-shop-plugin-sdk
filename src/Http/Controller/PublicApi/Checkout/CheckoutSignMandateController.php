<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApi\Checkout;

use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\CheckoutSignMandate\CheckoutSignMandateRequest;
use App\Application\UseCase\CheckoutSignMandate\CheckoutSignMandateUseCase;
use OpenApi\Annotations as OA;
use Ozean12\Sepa\Client\DomainModel\Mandate\SepaMandateNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @IsGranted("ROLE_AUTHENTICATED_AS_CHECKOUT_USER")
 * @OA\Post(
 *     path="/checkout-session/{sessionUuid}/sign-mandate",
 *     operationId="checkout_session_sign_mandate",
 *     summary="checkout_session_sign_mandate",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Checkout Server"},
 *     x={"groups":{"private", "public"}},
 *
 *     @OA\Response(response=204, description="Mandate successfully signed."),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class CheckoutSignMandateController
{
    private CheckoutSignMandateUseCase $checkoutSignMandateUseCase;

    public function __construct(CheckoutSignMandateUseCase $useCase)
    {
        $this->checkoutSignMandateUseCase = $useCase;
    }

    public function execute(string $sessionUuid): void
    {
        try {
            $this->checkoutSignMandateUseCase->execute(
                new CheckoutSignMandateRequest($sessionUuid)
            );
        } catch (SepaMandateNotFoundException | OrderNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }
}
