<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApi;

use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\CheckoutUpdateOrder\CheckoutUpdateOrderRequest;
use App\Application\UseCase\CheckoutUpdateOrder\CheckoutUpdateOrderUseCase;
use App\Http\RequestTransformer\CreateOrder\AddressRequestFactory;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @IsGranted("ROLE_AUTHENTICATED_AS_CHECKOUT_USER")
 * @OA\Post(
 *     path="/checkout-session/{sessionUuid}/update",
 *     operationId="checkout_session_update",
 *     summary="Checkout Session Update",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Checkout Client"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Parameter(in="path", name="sessionUuid", @OA\Schema(ref="#/components/schemas/UUID"), required=true),
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(ref="#/components/schemas/CheckoutUpdateOrderRequest"))
 *     ),
 *
 *     @OA\Response(response=204, description="Order data successfully updated"),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class CheckoutUpdateOrderController
{
    private $useCase;

    private $addressRequestFactory;

    public function __construct(
        CheckoutUpdateOrderUseCase $checkoutSessionUseCase,
        AddressRequestFactory $addressRequestFactory
    ) {
        $this->useCase = $checkoutSessionUseCase;
        $this->addressRequestFactory = $addressRequestFactory;
    }

    public function execute(Request $request, string $sessionUuid): void
    {
        $useCaseRequest = (new CheckoutUpdateOrderRequest())
            ->setSessionUuid($sessionUuid)
            ->setBillingAddress($this->addressRequestFactory->create($request, 'billing_address'));

        try {
            $this->useCase->execute($useCaseRequest);
        } catch (OrderNotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        }
    }
}
