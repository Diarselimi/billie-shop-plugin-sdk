<?php

namespace App\Http\Controller;

use App\Application\Exception\OrderNotFoundException;
use App\Application\Exception\OrderWorkflowException;
use App\Application\UseCase\ApproveOrder\ApproveOrderRequest;
use App\Application\UseCase\ApproveOrder\ApproveOrderUseCase;
use App\Http\HttpConstantsInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApproveOrderController
{
    private $useCase;

    public function __construct(ApproveOrderUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(string $externalCode, Request $request): Response
    {
        try {
            $useCaseRequest = new ApproveOrderRequest(
                $externalCode,
                $request->headers->get(HttpConstantsInterface::REQUEST_HEADER_API_USER)
            );

            $this->useCase->execute($useCaseRequest);
        } catch (OrderNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        } catch (OrderWorkflowException $e) {
            throw new AccessDeniedHttpException($e->getMessage());
        }

        return new Response();
    }
}
