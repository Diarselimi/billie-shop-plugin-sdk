<?php

namespace App\DomainModel\OrderInvoice;

use App\Application\UseCase\HttpInvoiceUpload\HttpInvoiceUploadException;
use App\DomainModel\FileService\FileServiceInterface;
use App\DomainModel\FileService\FileServiceRequestException;
use App\DomainModel\Order\OrderEntity;
use App\Infrastructure\ClientResponseDecodeException;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class OrderInvoiceManager implements LoggingInterface
{
    use LoggingTrait;

    /**
     * @var array|InvoiceUploadHandlerInterface[]
     */
    private array $uploadHandlers;

    private FileServiceInterface $fileService;

    private LegacyOrderInvoiceRepositoryInterface $orderInvoiceRepository;

    private LegacyOrderInvoiceFactory $orderInvoiceFactory;

    public function __construct(
        array $invoiceUploadHandlers,
        FileServiceInterface $fileService,
        LegacyOrderInvoiceRepositoryInterface $orderInvoiceRepository,
        LegacyOrderInvoiceFactory $orderInvoiceFactory
    ) {
        $this->uploadHandlers = $invoiceUploadHandlers;
        $this->fileService = $fileService;
        $this->orderInvoiceRepository = $orderInvoiceRepository;
        $this->orderInvoiceFactory = $orderInvoiceFactory;
    }

    public function upload(OrderEntity $order, string $invoiceUrl, string $invoiceNumber, string $event): void
    {
        foreach ($this->uploadHandlers as $name => $handler) {
            if ($handler->supports($order->getMerchantId())) {
                $this->logInfo('Handling invoice {number} using {name} handler', [
                    LoggingInterface::KEY_NUMBER => $invoiceNumber,
                    LoggingInterface::KEY_NAME => $name,
                ]);

                $handler->handleInvoice($order, $invoiceUrl, $invoiceNumber, $event);

                return;
            }
        }

        $this->logInfo('No supported handler for {number} found', [
            LoggingInterface::KEY_NUMBER => $invoiceNumber,
        ]);

        throw new OrderInvoiceUploadException('No supported handler found');
    }

    public function uploadInvoiceFile(OrderEntity $order, UploadedFile $uploadedFile): void
    {
        try {
            $file = $this->fileService->uploadFromFile($uploadedFile, $uploadedFile->getClientOriginalName(), FileServiceInterface::TYPE_ORDER_INVOICE);
        } catch (FileServiceRequestException | ClientResponseDecodeException $exception) {
            throw new HttpInvoiceUploadException($exception->getMessage(), null, $exception);
        }

        $orderInvoice = $this->orderInvoiceFactory->create($order->getId(), $file->getFileId(), $order->getInvoiceNumber());
        $this->orderInvoiceRepository->insert($orderInvoice);

        $this->logInfo('Invoice {number} for order id {id} uploaded with file id {count}', [
            LoggingInterface::KEY_NUMBER => $order->getInvoiceNumber(),
            LoggingInterface::KEY_ID => $order->getId(),
            LoggingInterface::KEY_COUNT => $file->getFileId(),
        ]);
    }
}
