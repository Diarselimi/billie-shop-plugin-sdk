<?php

namespace App\Http\Controller\PublicApi;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\WorkflowException;
use App\Application\UseCase\ConfirmPreApproveOrder\ConfirmPreApprovedOrderUseCase;
use App\Application\UseCase\ConfirmPreApproveOrder\ConfirmPreApprovedOrderRequest;
use App\Http\HttpConstantsInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use OpenApi\Annotations as OA;

/**
 * @IsGranted("ROLE_AUTHENTICATED_AS_MERCHANT")
 *
 * @OA\Post(
 *     path="/order/{uuid}/confirm",
 *     operationId="order_pre_approve_confirmation",
 *     summary="Pre-approve Order Confirm",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Back-end Order Creation"},
 *     x={"groups":{"public", "private"}},
 *
 *     @OA\Parameter(in="path", name="uuid", @OA\Schema(oneOf={@OA\Schema(ref="#/components/schemas/UUID"), @OA\Schema(type="string")}), required=true),
 *
 *     @OA\Response(response=202, description="Order was confirmed and now it's in state created.", @OA\JsonContent(ref="#/components/schemas/OrderResponse")),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class PreApprovedOrderConfirmController
{
    private $useCase;

    public function __construct(ConfirmPreApprovedOrderUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(string $uuid, Request $request): JsonResponse
    {
        $orderRequest = new ConfirmPreApprovedOrderRequest(
            $uuid,
            $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID)
        );

        try {
            $response = $this->useCase->execute($orderRequest);
        } catch (OrderNotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage(), $exception);
        } catch (WorkflowException $exception) {
            throw new BadRequestHttpException("The order is not in pre approved state to be confirmed", $exception);
        }

        return new JsonResponse($response->toArray(), JsonResponse::HTTP_OK);
    }
}
