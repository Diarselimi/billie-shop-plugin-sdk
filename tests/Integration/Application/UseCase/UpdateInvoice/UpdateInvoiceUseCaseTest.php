<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application\UseCase\UpdateInvoice;

use App\Application\Exception\RequestValidationException;
use App\Application\UseCase\UpdateInvoice\UpdateInvoiceRequest;
use App\Application\UseCase\UpdateInvoice\UpdateInvoiceUseCase;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\DomainModel\Invoice\Invoice;
use App\DomainModel\Invoice\InvoiceContainer;
use App\DomainModel\Invoice\InvoiceContainerFactory;
use App\DomainModel\Order\OrderContainer\OrderContainer;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepository;
use App\DomainModel\OrderFinancialDetails\OrderFinancialDetailsEntity;
use App\DomainModel\OrderInvoiceDocument\UploadHandler\InvoiceDocumentUploadHandlerAggregator;
use App\Tests\Helpers\FakeDataFiller;
use App\Tests\Integration\IntegrationTestCase;
use Prophecy\Argument;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateInvoiceUseCaseTest extends IntegrationTestCase
{
    use ValidatedUseCaseTrait;

    use FakeDataFiller;

    private UpdateInvoiceUseCase $useCase;

    public function setUp(): void
    {
        parent::setUp();

        $invoiceDocumentUploader = $this->prophesize(InvoiceDocumentUploadHandlerAggregator::class);
        $invoiceContainerFactory = $this->prophesize(InvoiceContainerFactory::class);
        $orderRepo = $this->prophesize(OrderRepository::class);
        $invoiceContainer = $this->prophesize(InvoiceContainer::class);
        $orderContainer = $this->prophesize(OrderContainer::class);
        $bus = new MessageBus();

        $validator = $this->getContainer()->get(ValidatorInterface::class);
        $finantialDetails = $this->prophesize(OrderFinancialDetailsEntity::class);

        $finantialDetails->getDuration()->willReturn(30);

        $invoiceContainerFactory->createFromInvoiceAndMerchant(Argument::cetera())->willReturn($invoiceContainer);
        $invoiceContainer->getOrderContainer()->willReturn($orderContainer);

        $invoice = new Invoice();
        $this->fillObject($invoice);

        $orderEntity = new OrderEntity();
        $this->fillObject($orderEntity);

        $orderRepo->getOneById(Argument::any())->willReturn($orderEntity);

        $this->useCase = new UpdateInvoiceUseCase(
            $invoiceDocumentUploader->reveal(),
            $invoiceContainerFactory->reveal(),
            $bus
        );
        $this->useCase->setValidator($validator);
    }

    /** @test */
    public function shouldFailWhenNoneOfBodyParametersAreNotSend()
    {
        $request = new UpdateInvoiceRequest('non_existing_uuid', 1);
        $this->expectException(RequestValidationException::class);

        $this->useCase->execute($request);
    }
}
