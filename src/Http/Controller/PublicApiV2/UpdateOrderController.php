<?php

declare(strict_types=1);

namespace App\Http\Controller\PublicApiV2;

use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\UpdateOrder\UpdateOrderRequest;
use App\Application\UseCase\UpdateOrder\UpdateOrderUseCase;
use App\DomainModel\MerchantUser\MerchantUserNotFoundException;
use App\DomainModel\OrderUpdate\UpdateOrderException;
use App\Http\Authentication\UserProvider;
use App\Support\TaxedMoneyFactoryDecorator;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @IsGranted("ROLE_AUTHENTICATED_AS_MERCHANT", "ROLE_UPDATE_ORDERS")
 * @OA\Post(
 *     path="/orders/{uuid}",
 *     operationId="order_update_v2",
 *     summary="Update Order",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Order Management"},
 *     x={"groups":{"publicV2"}},
 *
 *     @OA\Parameter(in="path", name="uuid", @OA\Schema(ref="#/components/schemas/UUID"), required=true),
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(ref="#/components/schemas/UpdateOrderRequest"))
 *     ),
 *
 *     @OA\Response(response=204, description="Order successfully updated"),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class UpdateOrderController
{
    private UserProvider $userProvider;

    private UpdateOrderUseCase $useCase;

    public function __construct(UpdateOrderUseCase $useCase, UserProvider $userProvider)
    {
        $this->useCase = $useCase;
        $this->userProvider = $userProvider;
    }

    public function execute(string $uuid, Request $request): void
    {
        try {
            $useCaseInput = new UpdateOrderRequest(
                $uuid,
                $this->userProvider->getAuthenticatedMerchantUser()->getMerchant()->getId(),
                $request->request->get('external_code'),
                TaxedMoneyFactoryDecorator::createFromRequest($request)
            );
            $this->useCase->execute($useCaseInput);
        } catch (UpdateOrderException $e) {
            throw new BadRequestHttpException($e->getMessage());
        } catch (OrderNotFoundException | MerchantUserNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }
}
