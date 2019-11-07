<?php

namespace App\Application\UseCase\CheckoutSession;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\CheckoutSession\CheckoutSessionFactory;
use App\Infrastructure\Repository\CheckoutSessionRepository;

class CreateCheckoutSessionUseCase implements ValidatedUseCaseInterface
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

    public function execute(CreateCheckoutSessionRequest $request): CheckoutSessionResponse
    {
        $this->validateRequest($request);

        $sessionEntity = $this->checkoutSessionRepository->create(
            $this->checkoutSessionFactory
                ->createFromRequest($request->getMerchantDebtorExternalId(), $request->getMerchantId())
        );

        return new CheckoutSessionResponse($sessionEntity);
    }
}
