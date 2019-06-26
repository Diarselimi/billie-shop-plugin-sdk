<?php

namespace App\Application\UseCase\PauseOrderDunning;

use App\Application\UseCase\ValidatedRequestInterface;
use Symfony\Component\Validator\Constraints as Assert;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="PauseOrderDunningRequest", title="Pause Order Dunning", type="object", properties={
 *     @OA\Property(property="number_of_days", type="integer", nullable=false, minimum=1)
 * })
 */
class PauseOrderDunningRequest implements ValidatedRequestInterface
{
    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     */
    private $orderId;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     */
    private $merchantId;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     * @Assert\GreaterThan(value=0)
     */
    private $numberOfDays;

    public function __construct(string $orderId, int $merchantId, int $numberOfDays)
    {
        $this->orderId = $orderId;
        $this->merchantId = $merchantId;
        $this->numberOfDays = $numberOfDays;
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function getNumberOfDays(): int
    {
        return $this->numberOfDays;
    }
}
