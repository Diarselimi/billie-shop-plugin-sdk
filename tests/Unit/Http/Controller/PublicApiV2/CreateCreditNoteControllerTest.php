<?php

declare(strict_types=1);

namespace App\Tests\Unit\Http\Controller\PublicApiV2;

use App\Application\Exception\RequestValidationException;
use App\Application\UseCase\CreateCreditNote\CreateCreditNoteRequest;
use App\Application\UseCase\CreateCreditNote\CreateCreditNoteUseCase;
use App\DomainModel\Invoice\CreditNote\CreditNoteAmountExceededException;
use App\DomainModel\Invoice\CreditNote\CreditNoteAmountTaxExceededException;
use App\DomainModel\Invoice\CreditNote\CreditNoteNotAllowedException;
use App\DomainModel\Invoice\InvoiceNotFoundException;
use App\Http\Controller\PublicApiV2\CreateCreditNoteController;
use App\Http\HttpConstantsInterface;
use App\Http\RequestTransformer\UpdateOrder\UpdateOrderAmountRequestFactory;
use App\Tests\Unit\UnitTestCase;
use Ozean12\Money\Money;
use Ozean12\Money\TaxedMoney\TaxedMoney;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @see CreateCreditNoteController
 */
class CreateCreditNoteControllerTest extends UnitTestCase
{
    private const INVOICE_UUID = '72a74d7d-0bc7-45c3-8bb6-1e1d8aef1fc7';

    private const MERCHANT_ID = 123;

    /**
     * @test
     */
    public function shouldSucceedCallingUsecase(): void
    {
        $httpRequest = $this->createRequest();
        $useCase = $this->prophesize(CreateCreditNoteUseCase::class);
        $amountFactory = $this->prophesize(UpdateOrderAmountRequestFactory::class);
        $amountFactory->create(Argument::type(Request::class))
            ->shouldBeCalledOnce()
            ->willReturn(new TaxedMoney(new Money(0), new Money(0), new Money(0)));

        $useCase->execute(
            Argument::that(
                function (CreateCreditNoteRequest $request) {
                    self::assertEquals(self::INVOICE_UUID, $request->getInvoiceUuid());
                    self::assertEquals(self::MERCHANT_ID, $request->getMerchantId());

                    return true;
                }
            )
        )->shouldBeCalledOnce();

        $controller = new CreateCreditNoteController($useCase->reveal(), $amountFactory->reveal());
        $controller->execute(self::INVOICE_UUID, $httpRequest);
    }

    /**
     * @test
     * @param string $useCaseException
     * @param string $expectedException
     * @dataProvider shouldCatchUseCaseExceptionDataProvider
     */
    public function shouldCatchUseCaseException(string $useCaseException, string $expectedException): void
    {
        $httpRequest = $this->createRequest();
        $useCase = $this->prophesize(CreateCreditNoteUseCase::class);
        $amountFactory = $this->prophesize(UpdateOrderAmountRequestFactory::class);
        $amountFactory->create(Argument::type(Request::class))
            ->shouldBeCalledOnce()
            ->willReturn(new TaxedMoney(new Money(0), new Money(0), new Money(0)));

        $useCase->execute(Argument::type(CreateCreditNoteRequest::class))
            ->shouldBeCalled()
            ->willThrow($useCaseException);

        $controller = new CreateCreditNoteController($useCase->reveal(), $amountFactory->reveal());

        $this->expectException($expectedException);

        $controller->execute(self::INVOICE_UUID, $httpRequest);
    }

    public function shouldCatchUseCaseExceptionDataProvider(): array
    {
        return [
            [InvoiceNotFoundException::class, NotFoundHttpException::class],
            [CreditNoteNotAllowedException::class, AccessDeniedHttpException::class],
            [CreditNoteAmountExceededException::class, RequestValidationException::class],
            [CreditNoteAmountTaxExceededException::class, RequestValidationException::class],
        ];
    }

    private function createRequest(): Request
    {
        $request = Request::create('/');
        $request->attributes->set(
            HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID,
            self::MERCHANT_ID
        );

        return $request;
    }
}
