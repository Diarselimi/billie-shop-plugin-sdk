<?php

namespace spec\App\Application\UseCase\HttpInvoiceUpload;

use App\Application\UseCase\HttpInvoiceUpload\HttpInvoiceUploadException;
use App\Application\UseCase\HttpInvoiceUpload\HttpInvoiceUploadRequest;
use App\Application\UseCase\HttpInvoiceUpload\HttpInvoiceUploadUseCase;
use App\DomainModel\FileService\FileServiceInterface;
use App\DomainModel\FileService\FileServiceRequestException;
use App\DomainModel\FileService\FileServiceResponseDTO;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderNotFoundException;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderInvoiceDocument\InvoiceDocumentCreator;
use App\Infrastructure\ClientResponseDecodeException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class HttpInvoiceUploadUseCaseSpec extends ObjectBehavior
{
    private const INVOICE_URL = 'http://some.url';

    private const INVOICE_NUMBER = 'DE555';

    private const MERCHANT_ID = 60;

    private const INVOICE = 'invoice_contents';

    private const FILE_ID = 888;

    private const FILE_UUID = '8c1ba170-a36b-4498-a266-551e5f8d054b';

    private const ORDER_ID = 90;

    private const ORDER_EXTERNAL_CODE = 'ext_code';

    public function let(
        FileServiceInterface $fileService,
        OrderRepositoryInterface $orderRepository,
        InvoiceDocumentCreator $invoiceDocumentCreator
    ) {
        $this->beConstructedWith(
            $fileService,
            $orderRepository,
            $invoiceDocumentCreator,
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(HttpInvoiceUploadUseCase::class);
    }

    public function it_throws_exception_on_order_not_found(
        OrderRepositoryInterface $orderRepository
    ) {
        $request = new HttpInvoiceUploadRequest(1, 'ext-code', 'uuid', 'url', 'num', 'event-src');
        $orderRepository->getOneByMerchantIdAndExternalCodeOrUUID(
            $request->getOrderExternalCode(),
            $request->getMerchantId()
        )->willReturn(null);

        $this->shouldThrow(OrderNotFoundException::class)->during('execute', [$request]);
    }

    public function it_throws_upload_exception_on_file_service_exception(
        OrderRepositoryInterface $orderRepository,
        FileServiceInterface $fileService
    ) {
        $request = new HttpInvoiceUploadRequest(1, 'ext-code', 'uuid', 'url', 'num', 'event-src');
        $orderRepository->getOneByMerchantIdAndExternalCodeOrUUID(
            $request->getOrderExternalCode(),
            $request->getMerchantId()
        )->willReturn(new OrderEntity());

        $fileService->uploadFromUrl(Argument::cetera())->willThrow(FileServiceRequestException::class);

        $this->shouldThrow(HttpInvoiceUploadException::class)->during('execute', [$request]);
    }

    public function it_throws_upload_exception_on_file_service_decode_exception(
        OrderRepositoryInterface $orderRepository,
        FileServiceInterface $fileService
    ) {
        $request = new HttpInvoiceUploadRequest(1, 'ext-code', 'uuid', 'url', 'num', 'event-src');
        $orderRepository->getOneByMerchantIdAndExternalCodeOrUUID(
            $request->getOrderExternalCode(),
            $request->getMerchantId()
        )->willReturn(new OrderEntity());

        $fileService->uploadFromUrl(Argument::cetera())->willThrow(ClientResponseDecodeException::class);

        $this->shouldThrow(HttpInvoiceUploadException::class)->during('execute', [$request]);
    }

    public function it_does_everything_amazingly(
        OrderRepositoryInterface $orderRepository,
        FileServiceInterface $fileService
    ) {
        $orderId = 100;
        $request = new HttpInvoiceUploadRequest(1, 'ext-code', 'invoice-uuid', 'url', 'invoice-num', 'event-src');
        $orderRepository->getOneByMerchantIdAndExternalCodeOrUUID(
            $request->getOrderExternalCode(),
            $request->getMerchantId()
        )->willReturn((new OrderEntity())->setId($orderId));

        $file = new FileServiceResponseDTO(1, 'uuid', 'filename', 'filepath');

        $fileService->uploadFromUrl(
            $request->getInvoiceUrl(),
            $request->getInvoiceUrl(),
            FileServiceInterface::TYPE_ORDER_INVOICE,
            Argument::type('int')
        )->willReturn($file);

        $this->execute($request);
    }
}
