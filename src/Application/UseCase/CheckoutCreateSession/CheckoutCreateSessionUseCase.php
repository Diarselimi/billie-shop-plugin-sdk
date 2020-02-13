<?php

namespace App\Application\UseCase\CheckoutCreateSession;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\CheckoutSession\CheckoutSessionFactory;
use App\Infrastructure\Repository\CheckoutSessionRepository;

class CheckoutCreateSessionUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $checkoutSessionRepository;

    private $checkoutSessionFactory;

    public function __construct(
        CheckoutSessionRepository $checkoutSessionRepository,
        CheckoutSessionFactory $checkoutSessionFactory
    ) {
        $this->checkoutSessionRepository = $checkoutSessionRepository;
        $this->checkoutSessionFactory = $checkoutSessionFactory;
    }

    public function execute(CheckoutCreateSessionRequest $request): CheckoutCreateSessionResponse
    {
        $this->validateRequest($request);

        $sessionEntity = $this->checkoutSessionFactory->createFromRequest(
            $request->getMerchantDebtorExternalId(),
            $request->getMerchantId()
        );

        $this->checkoutSessionRepository->create($sessionEntity);

        return new CheckoutCreateSessionResponse($sessionEntity);
    }
}
