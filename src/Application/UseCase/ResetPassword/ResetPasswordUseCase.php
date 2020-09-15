<?php

declare(strict_types=1);

namespace App\Application\UseCase\ResetPassword;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\MerchantUser\AuthenticationServiceInterface;
use App\DomainModel\MerchantUser\AuthenticationServiceRequestException;

class ResetPasswordUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $authenticationService;

    public function __construct(AuthenticationServiceInterface $authenticationService)
    {
        $this->authenticationService = $authenticationService;
    }

    public function execute(ResetPasswordRequest $request): void
    {
        $this->validateRequest($request);

        try {
            $this->authenticationService->resetPassword($request->getPlainPassword(), $request->getToken());
        } catch (AuthenticationServiceRequestException $exception) {
            throw new ResetPasswordException();
        }
    }
}
