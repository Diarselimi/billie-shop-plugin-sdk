<?php

declare(strict_types=1);

namespace spec\App\Application\UseCase\AuthorizeSandbox;

use App\Application\UseCase\AuthorizeSandbox\AuthorizeSandboxUseCase;
use App\DomainModel\Merchant\MerchantRepository;
use App\DomainModel\MerchantUser\AuthenticationServiceInterface;
use App\DomainModel\MerchantUser\MerchantUserPermissionsService;
use App\DomainModel\MerchantUser\MerchantUserRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthorizeSandboxUseCaseSpec extends ObjectBehavior
{
    public function let(
        AuthenticationServiceInterface $authenticationService,
        MerchantRepository $merchantRepository,
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantUserPermissionsService $merchantUserPermissionsService
    ) {
        $this->beConstructedWith(...func_get_args());
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(AuthorizeSandboxUseCase::class);
    }

    public function it_throws_exception_for_invalid_token(
        AuthenticationServiceInterface $authenticationService
    ) {
        $authenticationService
            ->authorizeToken('token')
            ->willReturn(null);
        $this->shouldThrow(AuthenticationException::class)->during('execute', ['token']);
    }
}
