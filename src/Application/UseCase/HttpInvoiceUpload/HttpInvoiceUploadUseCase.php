<?php

namespace App\Application\UseCase\HttpInvoiceUpload;

use App\DomainModel\FileService\FileServiceInterface;
use App\DomainModel\FileService\FileServiceRequestException;
use App\DomainModel\Order\OrderRepositoryInterface;
use App\DomainModel\OrderInvoice\OrderInvoiceFactory;
use App\DomainModel\OrderInvoice\OrderInvoiceRepositoryInterface;
use App\Infrastructure\ClientResponseDecodeException;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;

class HttpInvoiceUploadUseCase implements LoggingInterface
{
    use LoggingTrait;

    private const FILE_SIZE_LIMIT = 2097152; // 2 MBs

    private $client;

    private $fileService;

    private $orderRepository;

    private $orderInvoiceRepository;

    private $orderInvoiceFactory;

    public function __construct(
        Client $invoiceDownloadClient,
        FileServiceInterface $fileService,
        OrderRepositoryInterface $orderRepository,
        OrderInvoiceRepositoryInterface $orderInvoiceRepository,
        OrderInvoiceFactory $orderInvoiceFactory
    ) {
        $this->client = $invoiceDownloadClient;
        $this->fileService = $fileService;
        $this->orderRepository = $orderRepository;
        $this->orderInvoiceRepository = $orderInvoiceRepository;
        $this->orderInvoiceFactory = $orderInvoiceFactory;
    }

    public function execute(HttpInvoiceUploadRequest $request)
    {
        $fileUrl = $request->getInvoiceUrl();

        try {
            $response = $this->client->head($fileUrl);

            $size = $response->getHeader('Content-Length');
            if (!isset($size[0]) || $size[0] > self::FILE_SIZE_LIMIT) {
                throw new HttpInvoiceUploadException('Invoice file size limit exceeded');
            }

            $response = $this->client->get($fileUrl);
        } catch (TransferException $exception) {
            throw new HttpInvoiceUploadException('Invoice download transfer exception', null, $exception);
        }

        try {
            $file = $this->fileService->upload((string) $response->getBody(), $fileUrl);
        } catch (FileServiceRequestException | ClientResponseDecodeException $exception) {
            throw new HttpInvoiceUploadException('Exception wile uploading invoice to file service', null, $exception);
        }

        $order = $this->orderRepository->getOneByMerchantIdAndExternalCodeOrUUID($request->getOrderExternalCode(), $request->getMerchantId());
        $orderInvoice = $this->orderInvoiceFactory->create($order->getId(), $file->getFileId(), $request->getInvoiceNumber());
        $this->orderInvoiceRepository->insert($orderInvoice);

        $this->logInfo('Invoice {invoice_number} for order {order_id} uploaded with file id {file_id}', [
           'invoice_number' => $request->getInvoiceNumber(),
           'order_id' => $order->getId(),
           'file_id' => $file->getFileId(),
        ]);
    }
}
