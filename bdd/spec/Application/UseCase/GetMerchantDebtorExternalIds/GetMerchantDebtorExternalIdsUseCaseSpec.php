<?php

declare(strict_types=1);

namespace spec\App\Application\UseCase\GetMerchantDebtorExternalIds;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\UseCase\GetMerchantDebtorExternalIds\GetMerchantDebtorExternalIdsRequest;
use App\DomainModel\MerchantDebtor\MerchantDebtorEntity;
use App\DomainModel\MerchantDebtor\MerchantDebtorRepositoryInterface;
use App\Infrastructure\Repository\DebtorExternalDataRepository;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GetMerchantDebtorExternalIdsUseCaseSpec extends ObjectBehavior
{
    public function let(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        DebtorExternalDataRepository $debtorExternalDataRepository,
        ValidatorInterface $validator
    ): void {
        $this->beConstructedWith(...func_get_args());
        $this->setValidator($validator);
        $validator->validate(Argument::cetera())->willReturn(new ConstraintViolationList());
    }

    public function it_should_return_external_ids(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        DebtorExternalDataRepository $debtorExternalDataRepository
    ): void {
        $merchantId = 1;
        $merchantDebtorUuid = Uuid::uuid4()->toString();
        $merchantDebtorId = 2;
        $merchantDebtor = (new MerchantDebtorEntity())->setId($merchantDebtorId);
        $merchantDebtorRepository
            ->getOneByUuidAndMerchantId($merchantDebtorUuid, $merchantId)
            ->willReturn($merchantDebtor);
        $externalIds = ['some-external-id'];
        $debtorExternalDataRepository->getMerchantDebtorExternalIds($merchantDebtorId)->willReturn($externalIds);

        $this->execute(new GetMerchantDebtorExternalIdsRequest($merchantId, $merchantDebtorUuid))
            ->shouldBe($externalIds);
    }

    public function it_should_throw_exception_when_debtor_not_found(
        MerchantDebtorRepositoryInterface $merchantDebtorRepository,
        GetMerchantDebtorExternalIdsRequest $request
    ): void {
        $merchantDebtorRepository->getOneByUuidAndMerchantId(Argument::cetera())->willReturn(null);
        $this->shouldThrow(MerchantDebtorNotFoundException::class)
            ->during('execute', [
                new GetMerchantDebtorExternalIdsRequest(2, Uuid::uuid4()->toString()),
            ]);
    }
}
