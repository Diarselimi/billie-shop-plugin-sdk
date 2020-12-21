<?php

declare(strict_types=1);

namespace App\DomainModel\OrderInvoiceDocument;

use App\Application\UseCase\HttpInvoiceUpload\HttpInvoiceUploadException;
use App\DomainModel\FeatureFlag\FeatureFlagManager;
use App\DomainModel\FileService\FileServiceInterface;
use App\DomainModel\FileService\FileServiceRequestException;
use App\DomainModel\OrderInvoice\LegacyOrderInvoiceFactory;
use App\DomainModel\OrderInvoice\LegacyOrderInvoiceRepositoryInterface;
use App\Infrastructure\ClientResponseDecodeException;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Ozean12\Transfer\Message\Document\DocumentUploaded;
use Ozean12\Transfer\Shared\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\MessageBusInterface;

class InvoiceDocumentCreator implements LoggingInterface
{
    use LoggingTrait;

    private LegacyOrderInvoiceRepositoryInterface $orderInvoiceRepository;

    private LegacyOrderInvoiceFactory $orderInvoiceFactory;

    private FileServiceInterface $fileService;

    private FeatureFlagManager $featureFlagManager;

    private MessageBusInterface $messageBus;

    public function __construct(
        LegacyOrderInvoiceRepositoryInterface $orderInvoiceRepository,
        LegacyOrderInvoiceFactory $orderInvoiceFactory,
        FileServiceInterface $fileService,
        FeatureFlagManager $featureFlagManager,
        MessageBusInterface $messageBus
    ) {
        $this->orderInvoiceRepository = $orderInvoiceRepository;
        $this->orderInvoiceFactory = $orderInvoiceFactory;
        $this->fileService = $fileService;
        $this->featureFlagManager = $featureFlagManager;
        $this->messageBus = $messageBus;
    }

    public function create(InvoiceDocumentUpload $documentUpload): void
    {
        // invoices v1
        $invoice = $this->orderInvoiceFactory->create(
            $documentUpload->getOrderId(),
            $documentUpload->getFileId(),
            $documentUpload->getInvoiceNumber()
        );
        $this->orderInvoiceRepository->insert($invoice);
        $this->logInfo('Legacy Invoice created (invoice-butler disabled).');

        // TODO: re-enable when ready
        // $this->dispatchDocumentUploaded($documentUpload);
    }

    private function dispatchDocumentUploaded(InvoiceDocumentUpload $documentUpload)
    {
        // invoices v2
        $message = (new DocumentUploaded())
            ->setExternalCode($documentUpload->getInvoiceNumber())
            ->setFile(
                (new File())
                    ->setUuid($documentUpload->getFileUuid())
                    ->setObjectUuid($documentUpload->getInvoiceUuid())
                    ->setType($documentUpload->getType())
            );

        $this->messageBus->dispatch($message);
    }

    public function createFromUpload(
        int $orderId,
        string $invoiceUuid,
        string $invoiceNumber,
        UploadedFile $uploadedFile
    ): void {
        try {
            $file = $this->fileService->uploadFromFile(
                $uploadedFile,
                $uploadedFile->getClientOriginalName(),
                FileServiceInterface::TYPE_ORDER_INVOICE
            );
        } catch (FileServiceRequestException | ClientResponseDecodeException $exception) {
            throw new HttpInvoiceUploadException($exception->getMessage(), null, $exception);
        }

        $this->create(
            new InvoiceDocumentUpload(
                $orderId,
                $invoiceUuid,
                $invoiceNumber,
                $file->getUuid(),
                $file->getFileId()
            )
        );
    }
}
