<?php

namespace App\Application\UseCase\CreateOrder\Request;

use App\Application\Validator\Constraint as PaellaAssert;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(schema="CreateOrderAmountRequest", title="Order Amount", required={"net", "gross", "tax"},
 *     properties={
 *          @OA\Property(property="net", minimum=1, type="number", format="float", example=100.00),
 *          @OA\Property(property="gross", minimum=1, type="number", format="float", example=119.00),
 *          @OA\Property(property="tax", minimum=0, type="number", format="float", example=19.00),
 *     }
 * )
 */
class CreateOrderAmountRequest
{
    /**
     * @Assert\NotBlank()
     * @Assert\GreaterThan(value=0)
     * @PaellaAssert\Number()
     * @PaellaAssert\OrderAmounts()
     */
    private $net;

    /**
     * @Assert\NotBlank()
     * @Assert\GreaterThan(value=0)
     * @PaellaAssert\Number()
     * @PaellaAssert\OrderAmounts()
     */
    private $gross;

    /**
     * @Assert\NotBlank()
     * @Assert\GreaterThanOrEqual(value=0)
     * @PaellaAssert\Number()
     * @PaellaAssert\OrderAmounts()
     */
    private $tax;

    public function getNet(): ?float
    {
        return $this->net;
    }

    public function setNet($net): CreateOrderAmountRequest
    {
        $this->net = $net;

        return $this;
    }

    public function getGross(): ?float
    {
        return $this->gross;
    }

    public function setGross($gross): CreateOrderAmountRequest
    {
        $this->gross = $gross;

        return $this;
    }

    public function getTax(): ?float
    {
        return $this->tax;
    }

    public function setTax($tax): CreateOrderAmountRequest
    {
        $this->tax = $tax;

        return $this;
    }
}
