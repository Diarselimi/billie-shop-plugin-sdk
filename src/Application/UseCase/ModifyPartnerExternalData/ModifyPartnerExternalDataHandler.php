<?php

declare(strict_types=1);

namespace App\Application\UseCase\ModifyPartnerExternalData;

use App\Application\CommandHandler;
use App\Application\Exception\OrderNotFoundException;
use App\DomainModel\Order\OrderRepository;
use App\DomainModel\PartnerMerchant\PartnerExternalData;

class ModifyPartnerExternalDataHandler implements CommandHandler
{
    private OrderRepository $orderRepository;

    public function __construct(OrderRepository $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function execute(ModifyPartnerExternalDataCommand $command): void
    {
        $partnerExternalData = new PartnerExternalData(
            $command->getMerchantReference1(),
            $command->getMerchantReference2()
        );
        $order = $this->orderRepository->getOneByUuid($command->getOrderUuid());
        if ($order === null) {
            throw new OrderNotFoundException();
        }

        $order->setPartnerExternalData($partnerExternalData);
        $this->orderRepository->update($order);
    }
}
