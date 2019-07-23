<?php

namespace spec\App\Application\UseCase\HttpInvoiceUpload;

use App\Application\UseCase\HttpInvoiceUpload\HttpInvoiceUploadException;
use App\Application\UseCase\HttpInvoiceUpload\HttpInvoiceUploadRequest;
use App\Application\UseCase\HttpInvoiceUpload\HttpInvoiceUploadUseCase;
use App\DomainModel\FileService\FileServiceInterface;
use App\DomainModel\FileService\FileServiceRequestException;
use App\DomainModel\FileService\FileServiceResponseDTO;
use App\DomainModel\Order\OrderEntity;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderInvoice\OrderInvoiceEntity;
use App\DomainModel\OrderInvoice\OrderInvoiceFactory;
use App\DomainModel\OrderInvoice\OrderInvoiceRepositoryInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7\Response;
use PhpSpec\ObjectBehavior;
use Psr\Log\LoggerInterface;

class HttpInvoiceUploadUseCaseSpec extends ObjectBehavior
{
    private const INVOICE_URL = 'http://some.url';

    private const INVOICE_NUMBER = 'DE555';

    private const MERCHANT_ID = 60;

    private const INVOICE = 'invoice_contents';

    private const FILE_ID = 888;

    private const ORDER_ID = 90;

    private const ORDER_EXTERNAL_CODE = 'ext_code';

    public function let(
        Client $invoiceUploadClient,
        FileServiceInterface $fileService,
        OrderRepositoryInterface $orderRepository,
        OrderInvoiceRepositoryInterface $orderInvoiceRepository,
        OrderInvoiceFactory $orderInvoiceFactory,
        LoggerInterface $logger
    ) {
        $this->beConstructedWith(
            $invoiceUploadClient,
            $fileService,
            $orderRepository,
            $orderInvoiceRepository,
            $orderInvoiceFactory
        );

        $this->setLogger($logger);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(HttpInvoiceUploadUseCase::class);
    }

    public function it_throws_exception_on_invoice_download_exception(
        Client $invoiceUploadClient,
        FileServiceInterface $fileService,
        HttpInvoiceUploadRequest $request
    ) {
        $request->getInvoiceUrl()->shouldBeCalledOnce()->willReturn(self::INVOICE_URL);

        $invoiceUploadClient->head(self::INVOICE_URL)->shouldBeCalledOnce()->willThrow(new TransferException());
        $invoiceUploadClient->get(self::INVOICE_URL)->shouldNotBeCalled();
        $fileService->upload()->shouldNotBeCalled();

        $this->shouldThrow(new HttpInvoiceUploadException('Invoice download transfer exception'))->during('execute', [$request]);
    }

    public function it_throws_exception_if_file_is_too_big(
        Client $invoiceUploadClient,
        FileServiceInterface $fileService,
        HttpInvoiceUploadRequest $request,
        Response $response
    ) {
        $request->getInvoiceUrl()->shouldBeCalledOnce()->willReturn(self::INVOICE_URL);

        $response->getHeader('Content-Length')->shouldBeCalledOnce()->willReturn([2097153]); // 2MBs + 1 byte

        $invoiceUploadClient->head(self::INVOICE_URL)->shouldBeCalledOnce()->willReturn($response);
        $invoiceUploadClient->get(self::INVOICE_URL)->shouldNotBeCalled();
        $fileService->upload()->shouldNotBeCalled();

        $this->shouldThrow(new HttpInvoiceUploadException('Invoice file size limit exceeded'))->during('execute', [$request]);
    }

    public function it_throws_exception_on_file_service_error(
        Client $invoiceUploadClient,
        FileServiceInterface $fileService,
        HttpInvoiceUploadRequest $request,
        Response $headResponse,
        Response $getResponse
    ) {
        $request->getInvoiceUrl()->shouldBeCalledOnce()->willReturn(self::INVOICE_URL);

        $headResponse->getHeader('Content-Length')->shouldBeCalledOnce()->willReturn([2097152]); // 2MBs
        $getResponse->getBody()->shouldBeCalledOnce()->willReturn(self::INVOICE);

        $invoiceUploadClient->head(self::INVOICE_URL)->shouldBeCalledOnce()->willReturn($headResponse);
        $invoiceUploadClient->get(self::INVOICE_URL)->shouldBeCalledOnce()->willReturn($getResponse);
        $fileService->upload(self::INVOICE, self::INVOICE_URL, 'order_invoice')->shouldBeCalledOnce()->willThrow(new FileServiceRequestException());

        $this->shouldThrow(new HttpInvoiceUploadException('Exception wile uploading invoice to file service'))->during('execute', [$request]);
    }

    public function it_does_everything_amazingly(
        Client $invoiceUploadClient,
        FileServiceInterface $fileService,
        OrderRepositoryInterface $orderRepository,
        OrderInvoiceRepositoryInterface $orderInvoiceRepository,
        OrderInvoiceFactory $orderInvoiceFactory,
        HttpInvoiceUploadRequest $request,
        OrderEntity $order,
        OrderInvoiceEntity $orderInvoice,
        Response $headResponse,
        Response $getResponse,
        FileServiceResponseDTO $file
    ) {
        $request->getInvoiceUrl()->shouldBeCalledOnce()->willReturn(self::INVOICE_URL);
        $request->getInvoiceNumber()->shouldBeCalledTimes(2)->willReturn(self::INVOICE_NUMBER);
        $request->getMerchantId()->shouldBeCalledOnce()->willReturn(self::MERCHANT_ID);
        $request->getOrderExternalCode()->shouldBeCalledOnce()->willReturn(self::ORDER_EXTERNAL_CODE);

        $headResponse->getHeader('Content-Length')->shouldBeCalledOnce()->willReturn([2097152]); // 2MBs
        $getResponse->getBody()->shouldBeCalledOnce()->willReturn(self::INVOICE);

        $file->getFileId()->shouldBeCalledTimes(2)->willReturn(self::FILE_ID);
        $order->getId()->shouldBeCalledTimes(2)->willReturn(self::ORDER_ID);

        $invoiceUploadClient->head(self::INVOICE_URL)->shouldBeCalledOnce()->willReturn($headResponse);
        $invoiceUploadClient->get(self::INVOICE_URL)->shouldBeCalledOnce()->willReturn($getResponse);

        $fileService->upload(self::INVOICE, self::INVOICE_URL, 'order_invoice')->shouldBeCalledOnce()->willReturn($file);

        $orderRepository->getOneByMerchantIdAndExternalCodeOrUUID(self::ORDER_EXTERNAL_CODE, self::MERCHANT_ID)->willReturn($order);
        $orderInvoiceFactory->create(self::ORDER_ID, self::FILE_ID, self::INVOICE_NUMBER)->shouldBeCalledOnce()->willReturn($orderInvoice);
        $orderInvoiceRepository->insert($orderInvoice)->shouldBeCalledOnce();

        $this->execute($request);
    }
}
