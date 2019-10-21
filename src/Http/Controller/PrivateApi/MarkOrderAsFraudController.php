<?php

namespace App\Http\Controller\PrivateApi;

use App\Application\Exception\FraudOrderException;
use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\MarkOrderAsFraud\FraudReclaimActionException;
use App\Application\UseCase\MarkOrderAsFraud\MarkOrderAsFraudRequest;
use App\Application\UseCase\MarkOrderAsFraud\MarkOrderAsFraudUseCase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use OpenApi\Annotations as OA;

/**
 * @OA\Post(
 *     path="/order/{uuid}/mark-as-fraud",
 *     operationId="mark_order_as_fraud",
 *     summary="Mark Order As Fraud",
 *
 *     tags={"Support"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Parameter(
 *          in="path",
 *          name="uuid",
 *          @OA\Schema(ref="#/components/schemas/UUID"),
 *          description="Order UUID",
 *          required=true
 *     ),
 *
 *     @OA\Response(response=204, description="Order Marked As Fraud"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=403, ref="#/components/responses/Forbidden"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class MarkOrderAsFraudController
{
    private $useCase;

    public function __construct(MarkOrderAsFraudUseCase $markOrderAsFraudUseCase)
    {
        $this->useCase = $markOrderAsFraudUseCase;
    }

    public function execute(string $uuid): void
    {
        try {
            $useCaseRequest = new MarkOrderAsFraudRequest($uuid);
            $this->useCase->execute($useCaseRequest);
        } catch (FraudOrderException | FraudReclaimActionException $e) {
            throw new AccessDeniedHttpException($e->getMessage());
        } catch (OrderNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }
    }
}
