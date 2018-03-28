<?php

namespace App\Http\Controller;

use App\Application\UseCase\CreateOrder\CreateOrderRequest;
use App\Application\UseCase\CreateOrder\CreateOrderUseCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class CreateOrderController
{
    private $useCase;

    public function __construct(CreateOrderUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(Request $request)
    {
        $request = (new CreateOrderRequest($request->request->all()));
        $this->useCase->execute($request);

        return new Response(null, Response::HTTP_CREATED);
    }
}
