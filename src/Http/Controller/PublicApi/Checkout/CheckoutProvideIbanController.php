<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApi\Checkout;

use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\CheckoutProvideIban\CheckoutProvideIbanFailedException;
use App\Application\UseCase\CheckoutProvideIban\CheckoutProvideIbanNotAllowedException;
use App\Application\UseCase\CheckoutProvideIban\CheckoutProvideIbanRequest;
use App\Application\UseCase\CheckoutProvideIban\CheckoutProvideIbanUseCase;
use App\Http\Response\CheckoutProvideIbanResponsePayload;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @IsGranted("ROLE_AUTHENTICATED_AS_CHECKOUT_USER")
 * @OA\Post(
 *     path="/checkout-session/{sessionUuid}/iban",
 *     operationId="checkout_session_iban",
 *     summary="Provide an IBAN for the checkout session (for DirectDebit payment)",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Checkout Server"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Parameter(in="path", name="sessionUuid", @OA\Schema(ref="#/components/schemas/UUID"), required=true),

 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json", @OA\Schema(ref="#/components/schemas/CheckoutProvideIbanRequest"))
 *     ),
 *
 *     @OA\Response(response=200, description="SEPA Mandate is created", @OA\JsonContent(ref="#/components/schemas/CheckoutProvideIbanResponse")),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=403, description="IBAN is not acceptable"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class CheckoutProvideIbanController
{
    private CheckoutProvideIbanUseCase $useCase;

    public function __construct(CheckoutProvideIbanUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(Request $request, string $sessionUuid): CheckoutProvideIbanResponsePayload
    {
        $input = new CheckoutProvideIbanRequest(
            $sessionUuid,
            preg_replace('/\s+/', '', $request->request->get('iban')),
            $request->request->get('bank_account_owner')
        );

        try {
            return new CheckoutProvideIbanResponsePayload($this->useCase->execute($input));
        } catch (OrderNotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        } catch (CheckoutProvideIbanNotAllowedException $exception) {
            throw new AccessDeniedHttpException($exception->getMessage(), $exception);
        } catch (CheckoutProvideIbanFailedException $exception) {
            throw new BadRequestHttpException('IBAN is not supported', $exception);
        }
    }
}
