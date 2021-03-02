<?php

namespace spec\App\Application\UseCase\SignatoryPowersSelection;

use App\Application\UseCase\SignatoryPowersSelection\SignatoryPowersSelectionRequest;
use App\DomainModel\DebtorCompany\CompaniesServiceInterface;
use App\DomainModel\MerchantOnboarding\MerchantStepTransitionService;
use App\DomainModel\MerchantUser\MerchantUserEntity;
use App\DomainModel\MerchantUser\MerchantUserRepositoryInterface;
use App\DomainModel\SignatoryPower\SignatoryPowerAlreadySignedException;
use App\DomainModel\SignatoryPower\SignatoryPowerSelectionDTO;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SignatoryPowersSelectionUseCaseSpec extends ObjectBehavior
{
    public function let(
        CompaniesServiceInterface $companiesService,
        MerchantUserRepositoryInterface $merchantUserRepository,
        MerchantStepTransitionService $stepTransitionService,
        LoggerInterface $logger,
        ValidatorInterface $validator
    ): void {
        $this->beConstructedWith($companiesService, $merchantUserRepository, $stepTransitionService);
        $this->setLogger($logger);
        $this->setValidator($validator);

        $validator->validate(Argument::cetera())->willReturn(new ConstraintViolationList());
    }

    public function it_should_log_warning_when_already_signed(
        CompaniesServiceInterface $companiesService,
        MerchantStepTransitionService $stepTransitionService,
        LoggerInterface $logger,
        SignatoryPowersSelectionRequest $selectionsRequest
    ): void {
        $stepTransitionService->transition(Argument::cetera());
        $signatoryUuid = Uuid::uuid4()->toString();
        $signatoryPowerSelectionDTO = (new SignatoryPowerSelectionDTO())->setUuid($signatoryUuid);
        $selectionsRequest->findSelectedAsLoggedInSignatory()->willReturn($signatoryPowerSelectionDTO);
        $selectionsRequest->getMerchantUser()->willReturn(
            (new MerchantUserEntity())
                ->setId(1)
                ->setMerchantId(1)
        );
        $companyId = 2;
        $selectionsRequest->getCompanyId()->willReturn($companyId);
        $selectionsRequest->getSignatoryPowers()->willReturn([]);
        $companiesService->saveSelectedSignatoryPowers($companyId)->shouldBeCalledOnce();
        $companiesService
            ->acceptSignatoryPowerTc($signatoryUuid)
            ->willThrow(SignatoryPowerAlreadySignedException::class);

        $logger->warning(
            'Signatory already signed',
            [LoggingInterface::KEY_UUID => $signatoryUuid]
        )->shouldBeCalledOnce();

        $this->execute($selectionsRequest);
    }
}
