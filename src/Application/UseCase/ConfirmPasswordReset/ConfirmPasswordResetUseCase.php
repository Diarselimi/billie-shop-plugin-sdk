<?php

declare(strict_types=1);

namespace App\Application\UseCase\ConfirmPasswordReset;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\MerchantUser\AuthenticationServiceInterface;
use App\DomainModel\MerchantUser\AuthenticationServiceRequestException;

class ConfirmPasswordResetUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $authenticationService;

    public function __construct(AuthenticationServiceInterface $authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }

    public function execute(ConfirmPasswordResetRequest $request): void
    {
        $this->validateRequest($request);

        try {
            $this->authenticationService->confirmPasswordResetToken($request->getToken());
        } catch (AuthenticationServiceRequestException $exception) {
            throw new ValidPasswordResetTokenNotFoundException();
        }
    }
}
