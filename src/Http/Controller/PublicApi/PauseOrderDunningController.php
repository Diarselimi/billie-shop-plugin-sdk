<?php

namespace App\Http\Controller\PublicApi;

use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\PauseOrderDunning\PauseOrderDunningException;
use App\Application\UseCase\PauseOrderDunning\PauseOrderDunningRequest;
use App\Application\UseCase\PauseOrderDunning\PauseOrderDunningUseCase;
use App\Http\HttpConstantsInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use OpenApi\Annotations as OA;

/**
 * @OA\Post(
 *     path="/order/{id}/pause-dunning",
 *     operationId="order_pause_dunning",
 *     summary="Pause Order Dunning Process",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Order Management"},
 *     x={"groups":{"public", "private"}},
 *
 *     @OA\Parameter(in="path", name="id",
 *          @OA\Schema(oneOf={@OA\Schema(ref="#/components/schemas/UUID"), @OA\Schema(type="string")}),
 *          description="Order external code or UUID",
 *          required=true
 *     ),
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(ref="#/components/schemas/PauseOrderDunningRequest"))
 *     ),
 *
 *     @OA\Response(response=204, description="Successfully paused order dunning"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=403, description="Pause order dunning is not allowed", @OA\JsonContent(
 *          type="object",
 *          properties={
 *             @OA\Property(property="errors", type="array", @OA\Items(
 *                 type="object",
 *                 required={"title", "code"},
 *                 properties={
 *                    @OA\Property(
 *                        property="title",
 *                        type="string",
 *                        nullable=false,
 *                        enum={
 *                             "maximum pausing attempts reached",
 *                             "requested number of days exceeds maximum duration allowed",
 *                             "maximum dunning stage reached",
 *                             "other orders exceed maximum dunning stage",
 *                             "Cannot pause dunning. Order is not in state late"
 *                         },
 *                        example="maximum pausing attempts reached"
 *                    ),
 *                    @OA\Property(property="code", type="string", nullable=true, example="forbidden"),
 *                 }
 *             ))
 *          }
 *     )),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class PauseOrderDunningController
{
    private $useCase;

    public function __construct(PauseOrderDunningUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(string $id, Request $request)
    {
        try {
            $this->useCase->execute(
                new PauseOrderDunningRequest(
                    $id,
                    $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID),
                    $request->request->getInt('number_of_days')
                )
            );
        } catch (OrderNotFoundException $exception) {
            throw new NotFoundHttpException($exception->getMessage());
        } catch (PauseOrderDunningException $exception) {
            throw new AccessDeniedHttpException($exception->getMessage());
        }
    }
}
