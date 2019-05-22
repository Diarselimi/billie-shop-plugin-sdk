<?php

namespace App\Http\Controller;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\OrderWorkflowException;
use App\Application\UseCase\DeclineOrder\DeclineOrderRequest;
use App\Application\UseCase\DeclineOrder\DeclineOrderUseCase;
use App\Http\HttpConstantsInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DeclineOrderController
{
    private $useCase;

    public function __construct(DeclineOrderUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(string $id, Request $request): void
    {
        try {
            $useCaseRequest = new DeclineOrderRequest(
                $id,
                $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID)
            );

            $this->useCase->execute($useCaseRequest);
        } catch (OrderNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (OrderWorkflowException $e) {
            throw new AccessDeniedHttpException($e->getMessage());
        }
    }
}
