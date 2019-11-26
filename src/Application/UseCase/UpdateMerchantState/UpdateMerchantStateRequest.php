<?php

namespace App\Application\UseCase\UpdateMerchantState;

use App\Application\UseCase\ValidatedRequestInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(
 *      schema="UpdateMerchantStateRequest",
 *      type="object",
 *      properties={
 *          @OA\Property(property="state", type="string")
 *      }
 * )
 */
class UpdateMerchantStateRequest implements ValidatedRequestInterface
{
    /**
     * @Assert\NotBlank()
     * @Assert\Uuid()
     */
    private $merchantPaymentUuid;

    /**
     * @Assert\NotBlank()
     * @Assert\Type("string")
     */ // TODO: validate choice list, add enum to the OA docs

    private $state;

    public function __construct(string $merchantPaymentUuid, $state)
    {
        $this->merchantPaymentUuid = $merchantPaymentUuid;
        $this->state = $state;
    }

    public function getMerchantPaymentUuid(): string
    {
        return $this->merchantPaymentUuid;
    }

    public function getState(): string
    {
        return $this->state;
    }
}
