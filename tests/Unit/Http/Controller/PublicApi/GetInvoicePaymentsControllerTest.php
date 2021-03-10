<?php

declare(strict_types=1);

namespace App\Tests\Unit\Http\Controller\PublicApi;

use App\Application\Exception\InvoiceNotFoundException;
use App\Application\UseCase\GetInvoicePayments\GetInvoicePaymentsRequest;
use App\Application\UseCase\GetInvoicePayments\GetInvoicePaymentsUseCase;
use App\Application\UseCase\GetInvoicePayments\Response\GetInvoicePaymentsResponse;
use App\Http\Controller\PublicApi\GetInvoicePaymentsController;
use App\Http\HttpConstantsInterface;
use App\Tests\Unit\UnitTestCase;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @see GetInvoicePaymentsController
 */
class GetInvoicePaymentsControllerTest extends UnitTestCase
{
    private const INVOICE_UUID = '72a74d7d-0bc7-45c3-8bb6-1e1d8aef1fc7';

    private const MERCHANT_ID = 123;

    /**
     * @test
     */
    public function shouldSucceedCallingUsecase(): void
    {
        $httpRequest = $this->createRequest();
        $useCase = $this->prophesize(GetInvoicePaymentsUseCase::class);
        $expectedResponse = new GetInvoicePaymentsResponse();

        $useCase->execute(
            Argument::that(
                function (GetInvoicePaymentsRequest $request) {
                    self::assertEquals(self::INVOICE_UUID, $request->getInvoiceUuid());
                    self::assertEquals(self::MERCHANT_ID, $request->getMerchantId());

                    return true;
                }
            )
        )->shouldBeCalledOnce()->willReturn($expectedResponse);

        $controller = new GetInvoicePaymentsController($useCase->reveal());
        $actualResponse = $controller->execute(self::INVOICE_UUID, $httpRequest);

        self::assertSame($expectedResponse, $actualResponse);
    }

    /**
     * @test
     */
    public function shouldBe404IfInvoiceNotFound(): void
    {
        $httpRequest = $this->createRequest();
        $useCase = $this->prophesize(GetInvoicePaymentsUseCase::class);

        $useCase->execute(Argument::type(GetInvoicePaymentsRequest::class))
            ->shouldBeCalled()
            ->willReturn(new GetInvoicePaymentsResponse())
            ->willThrow(InvoiceNotFoundException::class);

        $controller = new GetInvoicePaymentsController($useCase->reveal());

        $this->expectException(NotFoundHttpException::class);

        $controller->execute(self::INVOICE_UUID, $httpRequest);
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
