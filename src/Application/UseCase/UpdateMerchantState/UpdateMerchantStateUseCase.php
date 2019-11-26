<?php

declare(strict_types=1);

namespace App\Application\UseCase\UpdateMerchantState;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;

class UpdateMerchantStateUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    public function __construct()
    {
    }

    public function execute(UpdateMerchantStateRequest $request): void
    {
        $this->validateRequest($request);

        //TODO: Implement logic
    }
}
