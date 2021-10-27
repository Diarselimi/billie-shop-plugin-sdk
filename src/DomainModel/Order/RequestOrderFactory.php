<?php

namespace App\DomainModel\Order;

use App\Application\UseCase\CreateOrder\CreateOrderRequestInterface;
use App\Helper\Uuid\UuidGeneratorInterface;

class RequestOrderFactory
{
    private UuidGeneratorInterface $uuidGenerator;

    public function __construct(UuidGeneratorInterface $uuidGenerator)
    {
        $this->uuidGenerator = $uuidGenerator;
    }

    public function createFromRequest(CreateOrderRequestInterface $request): OrderEntity
    {
        return (new OrderEntity())
            ->setExternalComment($request->getComment())
            ->setExternalCode($request->getExternalCode())
            ->setMerchantId($request->getMerchantId())
            ->setUuid($this->uuidGenerator->uuid4())
            ->setCheckoutSessionId($request->getCheckoutSessionId())
            ->setCreationSource($request->getCreationSource())
            ->setWorkflowName($request->getWorkflowName());
    }
}
