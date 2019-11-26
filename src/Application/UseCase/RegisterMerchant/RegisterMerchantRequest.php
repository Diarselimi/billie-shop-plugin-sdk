<?php

namespace App\Application\UseCase\RegisterMerchant;

use App\Application\UseCase\ValidatedRequestInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(
 *      schema="RegisterMerchantRequest",
 *      type="object",
 *      properties={
 *          @OA\Property(property="crefo_id", type="string"),
 *          @OA\Property(property="email", type="string", format="email")
 *      }
 * )
 */
class RegisterMerchantRequest implements ValidatedRequestInterface
{
    /**
     * @Assert\NotBlank()
     */
    private $crefoId;

    /**
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    public function __construct($crefoId, $email)
    {
        $this->crefoId = $crefoId;
        $this->email = $email;
    }

    public function getCrefoId(): string
    {
        return $this->crefoId;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
