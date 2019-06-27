<?php

namespace spec\App\Application\UseCase\MerchantUserLogin;

use App\Application\UseCase\MerchantUserLogin\MerchantUserLoginRequest;
use App\Application\UseCase\MerchantUserLogin\MerchantUserLoginResponse;
use App\Application\UseCase\MerchantUserLogin\MerchantUserLoginUseCase;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantUser\AuthenticationServiceAuthorizeTokenResponseDTO;
use App\DomainModel\MerchantUser\AuthenticationServiceInterface;
use App\DomainModel\MerchantUser\AuthenticationServiceTokenResponseDTO;
use App\DomainModel\MerchantUser\MerchantUserEntity;
use App\DomainModel\MerchantUser\MerchantUserRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MerchantUserLoginUseCaseSpec extends ObjectBehavior
{
    private const USER_ID = 60;

    private const MERCHANT_ID = 8;

    private const MERCHANT_USER_ID = 15;

    public function let(
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantRepositoryInterface $merchantRepository,
        AuthenticationServiceInterface $authenticationService,
        ValidatorInterface $validator,
        ConstraintViolationListInterface $constraintViolation
    ) {
        $constraintViolation->count()->willReturn(0);
        $validator->validate(Argument::any(), Argument::any(), Argument::any())->willReturn($constraintViolation);

        $this->beConstructedWith(
            $merchantUserRepository,
            $merchantRepository,
            $authenticationService
        );

        $this->setValidator($validator);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(MerchantUserLoginUseCase::class);
    }

    public function it_logs_in_the_user(
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantRepositoryInterface $merchantRepository,
        AuthenticationServiceInterface $authenticationService,
        MerchantUserLoginRequest $request,
        AuthenticationServiceTokenResponseDTO $token,
        AuthenticationServiceAuthorizeTokenResponseDTO $authorization,
        MerchantUserEntity $merchantUser,
        MerchantEntity $merchant
    ) {
        $request->getEmail()->willReturn('email');
        $request->getPassword()->willReturn('password');

        $token->getAccessToken()->willReturn('token');
        $token->getTokenType()->willReturn('type');

        $authorization->getUserId()->willReturn(self::USER_ID);

        $merchantUser->getRoles()->willReturn(['ROLE']);
        $merchantUser->getId()->willReturn(self::MERCHANT_USER_ID);
        $merchantUser->getMerchantId()->willReturn(self::MERCHANT_ID);

        $merchant->getName()->willReturn('Merchant');

        $authenticationService->requestUserToken('email', 'password')->shouldBeCalledOnce()->willReturn($token);
        $authenticationService->authorizeToken('type token')->shouldBeCalledOnce()->willReturn($authorization);

        $merchantUserRepository->getOneByUserId(self::USER_ID)->shouldBeCalledOnce()->willReturn($merchantUser);
        $merchantRepository->getOneById(self::MERCHANT_ID)->willReturn($merchant);

        $response = $this->execute($request);

        $response->shouldBeAnInstanceOf(MerchantUserLoginResponse::class);
        $response->getUserId()->shouldBe(self::MERCHANT_USER_ID);
        $response->getMerchantName()->shouldBe('Merchant');
        $response->getAccessToken()->shouldBe('token');
        $response->getRoles()->shouldBe(['ROLE']);
    }
}
