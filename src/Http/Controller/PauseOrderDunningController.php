<?php

namespace App\Http\Controller;

use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\PauseOrderDunning\PauseOrderDunningException;
use App\Application\UseCase\PauseOrderDunning\PauseOrderDunningRequest;
use App\Application\UseCase\PauseOrderDunning\PauseOrderDunningUseCase;
use App\Http\HttpConstantsInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PauseOrderDunningController
{
    private $useCase;

    public function __construct(PauseOrderDunningUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(string $id, Request $request): JsonResponse
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

        return new JsonResponse();
    }
}
