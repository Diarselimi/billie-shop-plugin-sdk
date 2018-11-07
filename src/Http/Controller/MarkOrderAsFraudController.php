<?php

namespace App\Http\Controller;

use App\Application\Exception\FraudOrderException;
use App\Application\Exception\OrderNotFoundException;
use App\Application\UseCase\MarkOrderAsFraud\MarkOrderAsFraudRequest;
use App\Application\UseCase\MarkOrderAsFraud\MarkOrderAsFraudUseCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MarkOrderAsFraudController
{
    private $useCase;

    public function __construct(MarkOrderAsFraudUseCase $markOrderAsFraudUseCase)
    {
        $this->useCase = $markOrderAsFraudUseCase;
    }

    public function execute(string $uuid): Response
    {
        try {
            $useCaseRequest = new MarkOrderAsFraudRequest($uuid);
            $this->useCase->execute($useCaseRequest);
        } catch (FraudOrderException $e) {
            throw new AccessDeniedHttpException($e->getMessage());
        } catch (OrderNotFoundException $e) {
            throw new NotFoundHttpException($e->getMessage());
        }

        return new Response();
    }
}
