<?php

namespace spec\App\Http\Controller\PublicApi\Dashboard;

use App\Application\UseCase\VerifyNewDebtorExternalCode\DebtorExternalCodeTakenException;
use App\Application\UseCase\VerifyNewDebtorExternalCode\VerifyNewDebtorExternalCodeRequest;
use App\Application\UseCase\VerifyNewDebtorExternalCode\VerifyNewDebtorExternalCodeUseCase;
use App\Http\HttpConstantsInterface;
use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class VerifyNewDebtorExternalCodeControllerSpec extends ObjectBehavior
{
    private const MERCHANT_ID = 1;

    private const EXTERNAL_CODE = 'external-code';

    public function let(VerifyNewDebtorExternalCodeUseCase $useCase)
    {
        $this->beConstructedWith(...func_get_args());
    }

    public function it_returns_successful_response(
        VerifyNewDebtorExternalCodeUseCase $useCase
    ) {
        $useCase
            ->execute((new VerifyNewDebtorExternalCodeRequest(self::MERCHANT_ID, self::EXTERNAL_CODE)))
            ->hasReturnVoid();

        $merchantKey = HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID;

        $this
            ->execute(self::EXTERNAL_CODE, new Request([], [], [$merchantKey => self::MERCHANT_ID]))
            ->shouldBe(null);
    }

    public function it_throws_exception(
        VerifyNewDebtorExternalCodeUseCase $useCase
    ) {
        $useCase
            ->execute((new VerifyNewDebtorExternalCodeRequest(self::MERCHANT_ID, self::EXTERNAL_CODE)))
            ->willThrow(new DebtorExternalCodeTakenException);

        $merchantKey = HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID;

        $this
            ->shouldThrow(new ConflictHttpException('Debtor external code already taken'))
            ->during('execute', [self::EXTERNAL_CODE, (new Request([], [], [$merchantKey => self::MERCHANT_ID]))]);
    }
}
