<?php

namespace spec\App\Application\UseCase\VerifyNewDebtorExternalCode;

use App\Application\UseCase\VerifyNewDebtorExternalCode\DebtorExternalCodeTakenException;
use App\Application\UseCase\VerifyNewDebtorExternalCode\VerifyNewDebtorExternalCodeRequest;
use App\Application\UseCase\VerifyNewDebtorExternalCode\VerifyNewDebtorExternalCodeUseCase;
use App\DomainModel\DebtorExternalData\DebtorExternalDataEntity;
use App\DomainModel\DebtorExternalData\DebtorExternalDataRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class VerifyNewDebtorExternalCodeUseCaseSpec extends ObjectBehavior
{
    private const MERCHANT_ID = 1;

    private const EXTERNAL_CODE = 'external-code';

    public function let(
        DebtorExternalDataRepositoryInterface $debtorExternalDataRepository,
        ValidatorInterface $validator
    ) {
        $this->beConstructedWith(...func_get_args());

        $validator->validate(Argument::cetera())->willReturn(new ConstraintViolationList());
        $this->setValidator($validator);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(VerifyNewDebtorExternalCodeUseCase::class);
    }

    public function it_confirms_valid_new_external_code(
        DebtorExternalDataRepositoryInterface $debtorExternalDataRepository
    ) {
        $debtorExternalDataRepository
            ->getOneByMerchantIdAndExternalCode(self::MERCHANT_ID, self::EXTERNAL_CODE)
            ->willReturn(null);

        $this->shouldNotThrow(DebtorExternalCodeTakenException::class)
            ->during('execute', [
                new VerifyNewDebtorExternalCodeRequest(self::MERCHANT_ID, self::EXTERNAL_CODE),
            ]);
    }

    public function it_finds_known_external_code(
        DebtorExternalDataRepositoryInterface $debtorExternalDataRepository
    ) {
        $debtorExternalDataRepository
            ->getOneByMerchantIdAndExternalCode(self::MERCHANT_ID, self::EXTERNAL_CODE)
            ->willReturn(new DebtorExternalDataEntity);

        $this->shouldThrow(DebtorExternalCodeTakenException::class)
            ->during('execute', [
                new VerifyNewDebtorExternalCodeRequest(self::MERCHANT_ID, self::EXTERNAL_CODE),
            ]);
    }
}
