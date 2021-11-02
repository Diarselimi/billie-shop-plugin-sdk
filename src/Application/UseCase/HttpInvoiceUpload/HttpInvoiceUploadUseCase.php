<?php

namespace App\Application\UseCase\HttpInvoiceUpload;

use App\DomainModel\FileService\FileServiceInterface;
use App\DomainModel\FileService\FileServiceRequestException;
use App\DomainModel\Order\OrderNotFoundException;
use App\DomainModel\Order\OrderRepository;
use App\DomainModel\OrderInvoiceDocument\InvoiceDocumentCreator;
use App\DomainModel\OrderInvoiceDocument\InvoiceDocumentUpload;
use App\Infrastructure\ClientResponseDecodeException;

class HttpInvoiceUploadUseCase
{
    private const FILE_SIZE_LIMIT = 2097152; // 2 MBs

    private FileServiceInterface $fileService;

    private OrderRepository $orderRepository;

    private InvoiceDocumentCreator $invoiceDocumentCreator;

    public function __construct(
        FileServiceInterface $fileService,
        OrderRepository $orderRepository,
        InvoiceDocumentCreator $invoiceDocumentCreator
    ) {
        $this->fileService = $fileService;
        $this->orderRepository = $orderRepository;
        $this->invoiceDocumentCreator = $invoiceDocumentCreator;
    }

    public function execute(HttpInvoiceUploadRequest $request)
    {
        $order = $this->orderRepository->getOneByMerchantIdAndExternalCodeOrUUID(
            $request->getOrderExternalCode(),
            $request->getMerchantId()
        );

        if ($order === null) {
            throw new OrderNotFoundException();
        }

        try {
            $file = $this->fileService->uploadFromUrl(
                $request->getInvoiceUrl(),
                $request->getInvoiceUrl(),
                FileServiceInterface::TYPE_ORDER_INVOICE,
                self::FILE_SIZE_LIMIT
            );
        } catch (FileServiceRequestException | ClientResponseDecodeException $exception) {
            throw new HttpInvoiceUploadException('Exception while uploading invoice to file service', null, $exception);
        }

        $this->invoiceDocumentCreator->create(new InvoiceDocumentUpload(
            $order->getId(),
            $request->getInvoiceUuid(),
            $request->getInvoiceNumber(),
            $file->getUuid(),
            $file->getFileId()
        ));
    }
}
