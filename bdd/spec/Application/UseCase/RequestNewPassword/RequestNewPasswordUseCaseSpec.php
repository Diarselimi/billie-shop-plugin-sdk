<?php

namespace spec\App\Application\UseCase\RequestNewPassword;

use App\Application\UseCase\RequestNewPassword\RequestNewPasswordRequest;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use App\DomainModel\MerchantUser\AuthenticationServiceInterface;
use App\DomainModel\MerchantUser\AuthenticationServiceRequestException;
use App\DomainModel\MerchantUser\MerchantUserEntity;
use App\DomainModel\MerchantUser\MerchantUserNotFoundException;
use App\DomainModel\MerchantUser\MerchantUserRepositoryInterface;
use App\DomainModel\PasswordResetRequest\PasswordResetRequestAnnouncer;
use App\DomainModel\PasswordResetRequest\RequestPasswordResetDTO;
use Billie\MonitoringBundle\Service\Alerting\Sentry\Raven\RavenClient;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestNewPasswordUseCaseSpec extends ObjectBehavior
{
    public function let(
        AuthenticationServiceInterface $authenticationService,
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantRepositoryInterface $merchantRepository,
        PasswordResetRequestAnnouncer $passwordResetRequestAnnouncer,
        ValidatorInterface $validator,
        LoggerInterface $logger,
        RavenClient $sentry
    ): void {
        $this->beConstructedWith(...func_get_args());
        $validator->validate(Argument::cetera())->willReturn(new ConstraintViolationList());
        $this->setValidator($validator);
        $this->setLogger($logger);
        $this->setSentry($sentry);
    }

    public function it_should_announce_password_reset_requested(
        AuthenticationServiceInterface $authenticationService,
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantRepositoryInterface $merchantRepository,
        PasswordResetRequestAnnouncer $passwordResetRequestAnnouncer
    ): void {
        $email = 'test@billie.dev';
        $userUuid = Uuid::uuid4()->toString();
        $token = 'someToken';
        $requestPasswordResetDTO = new RequestPasswordResetDTO($userUuid, $token);
        $authenticationService->requestNewPassword($email)->willReturn($requestPasswordResetDTO);
        $merchantId = 1;
        $firstName = 'Roel';
        $lastName = 'Philipsen';
        $merchantUserUuid = Uuid::uuid4()->toString();
        $user = (new MerchantUserEntity())
            ->setMerchantId($merchantId)
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setUuid($merchantUserUuid);
        $merchantUserRepository->getOneByUuid($userUuid)->willReturn($user);
        $merchantPaymentUuid = Uuid::uuid4()->toString();
        $merchant = (new MerchantEntity())->setPaymentUuid($merchantPaymentUuid);
        $merchantRepository->getOneById($merchantId)->willReturn($merchant);

        $passwordResetRequestAnnouncer->announcePasswordResetRequested(
            $merchantPaymentUuid,
            $token,
            $email,
            $firstName,
            $lastName,
            $merchantUserUuid
        )->shouldBeCalledOnce();

        $this->execute(new RequestNewPasswordRequest($email));
    }

    public function it_should_capture_authentication_exception(
        AuthenticationServiceInterface $authenticationService,
        RavenClient $sentry
    ): void {
        $exception = new AuthenticationServiceRequestException();
        $authenticationService
            ->requestNewPassword(Argument::cetera())
            ->willThrow($exception);
        $sentry->captureException($exception)->shouldBeCalledOnce();

        $this->execute(new RequestNewPasswordRequest(''));
    }

    public function it_should_not_log_to_sentry_when_user_not_found(
        AuthenticationServiceInterface $authenticationService,
        RavenClient $sentry
    ): void {
        $authenticationService
            ->requestNewPassword(Argument::cetera())
            ->willThrow(MerchantUserNotFoundException::class);
        $sentry->captureException(Argument::cetera())->shouldNotBeCalled();

        $this->execute(new RequestNewPasswordRequest(''));
    }
}
