<?php

declare(strict_types=1);

namespace App\Application\UseCase\RegisterMerchant;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;

class RegisterMerchantUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    public function __construct()
    {
    }

    public function execute(RegisterMerchantRequest $request): RegisterMerchantResponse
    {
        $this->validateRequest($request);

        //TODO: implement logic

        return new RegisterMerchantResponse();
    }
}
