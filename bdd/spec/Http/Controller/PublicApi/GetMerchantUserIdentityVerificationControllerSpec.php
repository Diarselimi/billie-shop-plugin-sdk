<?php

namespace spec\App\Http\Controller\PublicApi;

use App\Application\UseCase\GetMerchantUserIdentityVerification\GetMerchantUserIdentityVerificationResponse;
use App\Application\UseCase\GetMerchantUserIdentityVerification\GetMerchantUserIdentityVerificationUseCase;
use App\Application\UseCase\GetMerchantUserIdentityVerification\GetMerchantUserIdentityVerificationUseCaseException;
use App\Application\UseCase\GetMerchantUserIdentityVerification\IdentityVerificationCaseNotFoundException;
use App\DomainModel\IdentityVerification\IdentityVerificationCaseDTO;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GetMerchantUserIdentityVerificationControllerSpec extends ObjectBehavior
{
    public function let(GetMerchantUserIdentityVerificationUseCase $useCase): void
    {
        $this->beConstructedWith(...func_get_args());
    }

    public function it_should_return_response_successfully(
        GetMerchantUserIdentityVerificationUseCase $useCase
    ): void {
        $identityVerificationCaseDTO = new IdentityVerificationCaseDTO();
        $useCase->execute()->willReturn($identityVerificationCaseDTO);

        $this->execute()->shouldBe($identityVerificationCaseDTO);
    }

    public function it_should_throw_exception_when_no_case_found_in_db(
        GetMerchantUserIdentityVerificationUseCase $useCase
    ): void {
        $useCase->execute()->willThrow(IdentityVerificationCaseNotFoundException::class);

        $this->shouldThrow(NotFoundHttpException::class)->during('execute');
    }

    public function it_should_throw_exception_when_use_case_failed(
        GetMerchantUserIdentityVerificationUseCase $useCase
    ): void {
        $useCase->execute()->willThrow(GetMerchantUserIdentityVerificationUseCaseException::class);

        $this->shouldThrow(BadRequestHttpException::class)->during('execute');
    }
}
