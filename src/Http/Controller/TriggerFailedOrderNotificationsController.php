<?php

namespace App\Http\Controller;

use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\TriggerFailedOrderNotifications\TriggerFailedOrderNotificationsRequest;
use App\Application\UseCase\TriggerFailedOrderNotifications\TriggerFailedOrderNotificationsUseCase;
use App\Http\HttpConstantsInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TriggerFailedOrderNotificationsController
{
    private $useCase;

    public function __construct(TriggerFailedOrderNotificationsUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(string $id, Request $request): Response
    {
        try {
            $useCaseRequest = new TriggerFailedOrderNotificationsRequest(
                $id,
                $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID)
            );

            $this->useCase->execute($useCaseRequest);
        } catch (OrderNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        return new Response();
    }
}
