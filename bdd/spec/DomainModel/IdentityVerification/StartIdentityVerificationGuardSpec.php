<?php

namespace spec\App\DomainModel\IdentityVerification;

use App\Application\UseCase\StartIdentityVerification\StartIdentityVerificationException;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceRequestException;
use App\DomainModel\IdentityVerification\IdentityVerificationCaseDTO;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\MerchantUser\MerchantUserEntity;
use App\Http\Authentication\MerchantUser;
use App\Http\Authentication\UserProvider;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

class StartIdentityVerificationGuardSpec extends ObjectBehavior
{
    public function let(
        CompaniesServiceInterface $companiesService,
        UserProvider $userProvider
    ): void {
        $this->beConstructedWith(...func_get_args());
    }

    public function it_should_throw_exception_when_case_not_found(
        CompaniesServiceInterface $companiesService,
        UserProvider $userProvider
    ): void {
        $caseUuid = $this->mockUserProvider($userProvider);
        $companiesService
            ->getIdentityVerificationCase($caseUuid)
            ->willThrow(CompaniesServiceRequestException::class);

        $this
            ->shouldThrow(StartIdentityVerificationException::class)
            ->during('startIdentityVerificationAllowed');
    }

    public function it_should_not_allow_when_theres_valid_case(
        CompaniesServiceInterface $companiesService,
        UserProvider $userProvider,
        IdentityVerificationCaseDTO $identityVerificationCaseDTO
    ): void {
        $caseUuid = $this->mockUserProvider($userProvider);
        $identityVerificationCaseDTO->isValid()->willReturn(true);
        $companiesService->getIdentityVerificationCase($caseUuid)->willReturn($identityVerificationCaseDTO);

        $this->startIdentityVerificationAllowed()->shouldBe(false);
    }

    public function it_should_allow_when_theres_invalid_case(
        CompaniesServiceInterface $companiesService,
        UserProvider $userProvider,
        IdentityVerificationCaseDTO $identityVerificationCaseDTO
    ): void {
        $caseUuid = $this->mockUserProvider($userProvider);
        $identityVerificationCaseDTO->isValid()->willReturn(false);
        $companiesService->getIdentityVerificationCase($caseUuid)->willReturn($identityVerificationCaseDTO);

        $this->startIdentityVerificationAllowed()->shouldBe(true);
    }

    private function mockUserProvider(UserProvider $userProvider): string
    {
        $caseUuid = Uuid::uuid4()->toString();
        $merchantUser = new MerchantUser(
            new MerchantEntity(),
            '',
            (new MerchantUserEntity())->setIdentityVerificationCaseUuid($caseUuid),
            []
        );
        $userProvider->getMerchantUser()->willReturn($merchantUser);

        return $caseUuid;
    }
}
