<?php

declare(strict_types=1);

namespace App\Application\UseCase\CheckInvoiceOwner;

use App\DomainModel\OrderInvoice\OrderInvoiceRepositoryInterface;

class CheckInvoiceOwnerUseCase
{
    private OrderInvoiceRepositoryInterface $orderInvoiceRepository;

    public function __construct(OrderInvoiceRepositoryInterface $orderInvoiceRepository)
    {
        $this->orderInvoiceRepository = $orderInvoiceRepository;
    }

    public function execute(CheckInvoiceOwnerRequest $request): bool
    {
        $orderInvoice = $this->orderInvoiceRepository->getByUuidAndMerchant(
            $request->getInvoiceUuid(),
            $request->getMerchantId()
        );

        return $orderInvoice !== null;
    }
}
