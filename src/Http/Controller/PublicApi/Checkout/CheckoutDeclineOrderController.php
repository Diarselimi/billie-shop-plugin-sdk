<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApi\Checkout;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\WorkflowException;
use App\Application\UseCase\CheckoutDeclineOrder\CheckoutDeclineOrderRequest;
use App\Application\UseCase\CheckoutDeclineOrder\CheckoutDeclineOrderUseCase;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @IsGranted("ROLE_AUTHENTICATED_AS_CHECKOUT_USER")
 * @OA\Get(
 *     path="/checkout-session/{sessionUuid}/decline",
 *     operationId="checkout_session_decline",
 *     summary="Decline orders in authorised state.",
 *     security={{"oauth2"={}}},
 *     @OA\Parameter(in="query", name="reason",
 *          @OA\Schema(type="string"),
 *          description="Reason for declining the order.",
 *          required=false
 *     ),
 *
 *     tags={"Checkout Server"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Response(response=204, description="Order successfully cancelled"),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class CheckoutDeclineOrderController
{
    private CheckoutDeclineOrderUseCase $declineOrderUseCase;

    public function __construct(CheckoutDeclineOrderUseCase $declineOrderUseCase)
    {
        $this->declineOrderUseCase = $declineOrderUseCase;
    }

    public function execute(Request $request, string $sessionUuid): void
    {
        $reason = $request->get('reason', CheckoutDeclineOrderRequest::REASON_WRONG_IDENTIFICATION);

        try {
            $this->declineOrderUseCase->execute(
                new CheckoutDeclineOrderRequest($sessionUuid, $reason)
            );
        } catch (OrderNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (WorkflowException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }
}
