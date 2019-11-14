<?php

namespace App\Application\UseCase\GetInvitedMerchantUser;

use App\DomainModel\ArrayableInterface;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="GetInvitedMerchantUserResponse", type="object", properties={
 *      @OA\Property(property="email", type="string", format="email", nullable=false),
 * })
 */
class GetInvitedMerchantUserResponse implements ArrayableInterface
{
    private $email;

    public function __construct(string $email)
    {
        $this->email = $email;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function toArray(): array
    {
        return [
            'email' => $this->getEmail(),
        ];
    }
}
