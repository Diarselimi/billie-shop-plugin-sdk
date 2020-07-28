<?php

namespace spec\App\Application\UseCase\GetMerchantUserIdentityVerification;

use App\Application\UseCase\GetMerchantUserIdentityVerification\GetMerchantUserIdentityVerificationUseCaseException;
use App\Application\UseCase\GetMerchantUserIdentityVerification\IdentityVerificationCaseNotFoundException;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceRequestException;
use App\DomainModel\IdentityVerification\IdentityVerificationCaseDTO;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\MerchantUser\MerchantUserEntity;
use App\Http\Authentication\MerchantUser;
use App\Http\Authentication\UserProvider;
use App\Support\DateFormat;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;

class GetMerchantUserIdentityVerificationUseCaseSpec extends ObjectBehavior
{
    public function let(UserProvider $userProvider, CompaniesServiceInterface $companiesService)
    {
        $this->beConstructedWith(...func_get_args());
    }

    public function it_should_return_response_successfully(
        UserProvider $userProvider,
        CompaniesServiceInterface $companiesService
    ): void {
        $caseUuid = Uuid::uuid4()->toString();
        $this->mockUserProvider($userProvider, $caseUuid);
        $url = 'postident.de/?case=5';
        $validTill = new \DateTime('now +1 week');
        $caseStatus = 'closed';
        $identificationStatus = 'success';
        $identityVerificationCaseDTO = (new IdentityVerificationCaseDTO())
            ->setUrl($url)
            ->setValidTill($validTill)
            ->setCaseStatus($caseStatus)
            ->setIsCurrent(true)
            ->setIdentificationStatus($identificationStatus);
        $companiesService->getIdentityVerificationCase($caseUuid)->willReturn($identityVerificationCaseDTO);

        $this->execute($caseUuid)->toArray()->shouldBe([
            'url' => $url,
            'valid_till' => $validTill->format(DateFormat::FORMAT_YMD_HIS),
            'case_status' => $caseStatus,
            'identification_status' => $identificationStatus,
        ]);
    }

    public function it_should_throw_exception_when_no_case_linked(
        UserProvider $userProvider
    ): void {
        $this->mockUserProvider($userProvider);

        $this->shouldThrow(IdentityVerificationCaseNotFoundException::class)->during('execute');
    }

    public function it_should_throw_exception_on_companies_service_exception(
        UserProvider $userProvider,
        CompaniesServiceInterface $companiesService
    ): void {
        $this->mockUserProvider($userProvider, Uuid::uuid4()->toString());
        $companiesService
            ->getIdentityVerificationCase(Argument::any())
            ->willThrow(CompaniesServiceRequestException::class);

        $this->shouldThrow(GetMerchantUserIdentityVerificationUseCaseException::class)
            ->during('execute', ['someCaseId']);
    }

    public function it_should_throw_exception_when_no_valid_case_found(
        UserProvider $userProvider,
        CompaniesServiceInterface $companiesService,
        IdentityVerificationCaseDTO $identityVerificationCaseDTO
    ): void {
        $this->mockUserProvider($userProvider, Uuid::uuid4()->toString());
        $identityVerificationCaseDTO->isValid()->willReturn(false);
        $companiesService
            ->getIdentityVerificationCase(Argument::any())
            ->willReturn(
                (new IdentityVerificationCaseDTO())
                    ->setValidTill(new \DateTime())
                    ->setIsCurrent(false)
            );

        $this->shouldThrow(IdentityVerificationCaseNotFoundException::class)
            ->during('execute', ['someCaseId']);
    }

    private function mockUserProvider(UserProvider $userProvider, string $caseUuid = null): void
    {
        $merchantUser = new MerchantUser(
            new MerchantEntity(),
            '',
            (new MerchantUserEntity())->setIdentityVerificationCaseUuid($caseUuid),
            []
        );
        $userProvider->getMerchantUser()->willReturn($merchantUser);
    }
}
