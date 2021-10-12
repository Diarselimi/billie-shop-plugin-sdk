<?php

namespace spec\App\Application\UseCase\RegisterMerchant;

use App\Application\UseCase\RegisterMerchant\RegisterMerchantRequest;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\DebtorCompany\CompaniesServiceRequestException;
use App\DomainModel\DebtorCompany\IdentifyFirmenwissenFailedException;
use App\DomainModel\Merchant\MerchantCreationService;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationPersistenceService;
use App\Helper\Uuid\UuidGeneratorInterface;
use App\Infrastructure\Repository\MerchantPdoRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RegisterMerchantUseCaseSpec extends ObjectBehavior
{
    public function let(
        UuidGeneratorInterface $uuidGenerator,
        CompaniesServiceInterface $companiesService,
        MerchantPdoRepository $merchantRepository,
        MerchantCreationService $merchantCreationService,
        MerchantUserInvitationPersistenceService $invitationPersistenceService,
        ValidatorInterface $validator
    ) {
        $this->beConstructedWith(
            $uuidGenerator,
            $companiesService,
            $merchantRepository,
            $merchantCreationService,
            $invitationPersistenceService
        );

        $validator->validate(
            Argument::any(),
            Argument::any(),
            Argument::any()
        )->willReturn(new ConstraintViolationList());
        $this->setValidator($validator);
    }

    public function it_throws_exception_when_identify_firmenwissen_failed(CompaniesServiceInterface $companiesService)
    {
        $crefoId = '123';
        $companiesService->getDebtorsByCrefoId($crefoId)->willReturn([]);
        $companiesService->identifyFirmenwissen($crefoId)->willThrow(CompaniesServiceRequestException::class);

        $request = new RegisterMerchantRequest($crefoId, 'test@billie.dev');

        $this->shouldThrow(IdentifyFirmenwissenFailedException::class)->during('execute', [$request]);
    }
}
