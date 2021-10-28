<?php

namespace App\DomainModel\Order;

use App\Application\UseCase\CreateOrder\CreateOrderRequestInterface;
use App\Helper\Uuid\UuidGeneratorInterface;

class OrderFactory
{
    private UuidGeneratorInterface $uuidGenerator;

    public function __construct(UuidGeneratorInterface $uuidGenerator)
    {
        $this->uuidGenerator = $uuidGenerator;
    }

    public function createFromRequest(CreateOrderRequestInterface $request): OrderEntity
    {
        $expiration = $request->getExpiration() ? new \DateTimeImmutable($request->getExpiration()) : null;

        return (new OrderEntity($expiration))
            ->setExternalComment($request->getComment())
            ->setExternalCode($request->getExternalCode())
            ->setMerchantId($request->getMerchantId())
            ->setUuid($this->uuidGenerator->uuid4())
            ->setCheckoutSessionId($request->getCheckoutSessionId())
            ->setCreationSource($request->getCreationSource())
            ->setWorkflowName($request->getWorkflowName());
    }
}
