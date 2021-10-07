<?php

namespace App\Application\UseCase\InitiateCheckoutSession;

use App\Application\CommandHandler;
use App\DomainModel\CheckoutSession\CheckoutSession;
use App\DomainModel\CheckoutSession\CheckoutSessionRepository;

class InitiateCheckoutSessionHandler implements CommandHandler
{
    private CheckoutSessionRepository $repository;

    public function __construct(CheckoutSessionRepository $repository)
    {
        $this->repository = $repository;
    }

    public function execute(InitiateCheckoutSession $command): void
    {
        $newSession = new CheckoutSession(
            $command->token(),
            $command->country(),
            $command->merchantId()
        );

        $this->repository->save($newSession);
    }
}
