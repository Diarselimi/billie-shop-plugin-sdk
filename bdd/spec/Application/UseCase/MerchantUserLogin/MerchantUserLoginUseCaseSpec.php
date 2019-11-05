<?php

namespace spec\App\Application\UseCase\MerchantUserLogin;

use App\Application\UseCase\MerchantUserLogin\MerchantUserLoginRequest;
use App\Application\UseCase\MerchantUserLogin\MerchantUserLoginResponse;
use App\Application\UseCase\MerchantUserLogin\MerchantUserLoginUseCase;
use App\DomainModel\Address\AddressEntity;
use App\DomainModel\Address\AddressEntityFactory;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\DebtorCompany;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantUser\AuthenticationServiceAuthorizeTokenResponseDTO;
use App\DomainModel\MerchantUser\AuthenticationServiceInterface;
use App\DomainModel\MerchantUser\AuthenticationServiceTokenResponseDTO;
use App\DomainModel\MerchantUser\MerchantUserEntity;
use App\DomainModel\MerchantUser\MerchantUserPermissionsService;
use App\DomainModel\MerchantUser\MerchantUserRepositoryInterface;
use App\DomainModel\MerchantUser\MerchantUserRoleEntityFactory;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

class MerchantUserLoginUseCaseSpec extends ObjectBehavior
{
    private const USER_ID = 60;

    private const MERCHANT_ID = 8;

    private const MERCHANT_USER_UUID = 'f15a9deb-9fdd-4ff4-930c-c01fba3d3265';

    public function let(
        ValidatorInterface $validator,
        ConstraintViolationListInterface $constraintViolation,
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantRepositoryInterface $merchantRepository,
        CompaniesServiceInterface $companiesService,
        AddressEntityFactory $addressEntityFactory,
        MerchantUserPermissionsService $merchantUserPermissionsService,
        AuthenticationServiceInterface $authenticationService
    ) {
        $constraintViolation->count()->willReturn(0);
        $validator->validate(Argument::any(), Argument::any(), Argument::any())->willReturn($constraintViolation);
        $merchantUserPermissionsService->resolveUserRole(Argument::any())->willReturn(
            (new MerchantUserRoleEntityFactory())->create(1, 'test', 'test', ['ROLE'])
        );

        $this->beConstructedWith(
            $merchantUserRepository,
            $merchantRepository,
            $companiesService,
            $addressEntityFactory,
            $merchantUserPermissionsService,
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
        MerchantEntity $merchant,
        CompaniesServiceInterface $companiesService,
        AddressEntityFactory $addressEntityFactory
    ) {
        $request->getEmail()->willReturn('email');
        $request->getPassword()->willReturn('password');

        $token->getAccessToken()->willReturn('token');
        $token->getTokenType()->willReturn('type');

        $authorization->getUserId()->willReturn(self::USER_ID);
        $authorization->getEmail()->willReturn('email');

        $merchantUser->getPermissions()->willReturn(['ROLE']);
        $merchantUser->getId()->willReturn(1);
        $merchantUser->getUuid()->willReturn(self::MERCHANT_USER_UUID);
        $merchantUser->getMerchantId()->willReturn(self::MERCHANT_ID);
        $merchantUser->getFirstName()->willReturn('Foo');
        $merchantUser->getLastName()->willReturn('Bar');

        $merchant->getCompanyId()->willReturn(1);
        $companiesService->getDebtor(1)->willReturn(
            (new DebtorCompany())->setName('Merchant')
        );
        $addressEntityFactory->createFromDebtorCompany(Argument::any())->willReturn(
            (new AddressEntity())
                ->setCity('City')
                ->setHouseNumber('1')
                ->setCountry('DE')
                ->setPostalCode('102120')
                ->setStreet('Sesamestr.')
        );

        $authenticationService->requestUserToken('email', 'password')->shouldBeCalledOnce()->willReturn($token);
        $authenticationService->authorizeToken('type token')->shouldBeCalledOnce()->willReturn($authorization);

        $merchantUserRepository->getOneByUuid(self::USER_ID)->shouldBeCalledOnce()->willReturn($merchantUser);
        $merchantRepository->getOneById(self::MERCHANT_ID)->willReturn($merchant);

        $response = $this->execute($request);

        $response->shouldBeAnInstanceOf(MerchantUserLoginResponse::class);
        $response = $response->getWrappedObject()->toArray();
        /** @var array $response */
        Assert::keyExists($response, 'user');
        Assert::keyExists($response['user'], 'merchant_company');
        Assert::keyExists($response, 'access_token');
        Assert::eq($response['user']['uuid'], self::MERCHANT_USER_UUID);
        Assert::eq($response['user']['merchant_company']['name'], 'Merchant');
        Assert::eq($response['access_token'], 'token');
        Assert::eq($response['user']['permissions'], ['ROLE']);
    }
}
