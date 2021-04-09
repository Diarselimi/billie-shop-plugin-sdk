<?php

declare(strict_types=1);

namespace App\Tests\Unit\Http\Controller\PublicApiV2;

use App\Application\Exception\InvoiceNotFoundException;
use App\Application\Exception\RequestValidationException;
use App\Application\UseCase\ConfirmInvoicePayment\ConfirmInvoicePaymentNotAllowedException;
use App\Application\UseCase\ConfirmInvoicePayment\ConfirmInvoicePaymentRequest;
use App\Application\UseCase\ConfirmInvoicePayment\ConfirmInvoicePaymentUseCase;
use App\Application\UseCase\ConfirmInvoicePayment\AmountExceededException;
use App\Http\Controller\PublicApiV2\ConfirmInvoicePaymentController;
use App\Http\HttpConstantsInterface;
use App\Tests\Unit\UnitTestCase;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @see ConfirmInvoicePaymentController
 */
class ConfirmInvoicePaymentControllerTest extends UnitTestCase
{
    private const INVOICE_UUID = '72a74d7d-0bc7-45c3-8bb6-1e1d8aef1fc7';

    private const MERCHANT_ID = 123;

    /**
     * @test
     */
    public function shouldSucceedCallingUsecase(): void
    {
        $httpRequest = $this->createRequest();
        $useCase = $this->prophesize(ConfirmInvoicePaymentUseCase::class);

        $useCase->execute(
            Argument::that(
                function (ConfirmInvoicePaymentRequest $request) {
                    self::assertEquals(self::INVOICE_UUID, $request->getInvoiceUuid());
                    self::assertEquals(self::MERCHANT_ID, $request->getMerchantId());

                    return true;
                }
            )
        )->shouldBeCalledOnce();

        $controller = new ConfirmInvoicePaymentController($useCase->reveal());
        $controller->execute(self::INVOICE_UUID, $httpRequest);
    }

    /**
     * @test
     * @param  string                  $useCaseException
     * @param  string                  $expectedException
     * @throws AmountExceededException
     * @dataProvider shouldCatchUseCaseExceptionDataProvider
     */
    public function shouldCatchUseCaseException(string $useCaseException, string $expectedException): void
    {
        $httpRequest = $this->createRequest();
        $useCase = $this->prophesize(ConfirmInvoicePaymentUseCase::class);

        $useCase->execute(Argument::type(ConfirmInvoicePaymentRequest::class))
            ->shouldBeCalled()
            ->willThrow($useCaseException);

        $controller = new ConfirmInvoicePaymentController($useCase->reveal());

        $this->expectException($expectedException);

        $controller->execute(self::INVOICE_UUID, $httpRequest);
    }

    public function shouldCatchUseCaseExceptionDataProvider(): array
    {
        return [
            [InvoiceNotFoundException::class, NotFoundHttpException::class],
            [ConfirmInvoicePaymentNotAllowedException::class, AccessDeniedHttpException::class],
            [AmountExceededException::class, RequestValidationException::class],
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
