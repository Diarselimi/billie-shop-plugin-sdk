<?php

declare(strict_types=1);

namespace App\Application\UseCase\AuthorizeSandbox;

use App\DomainModel\ArrayableInterface;
use App\DomainModel\MerchantUser\MerchantUserEntity;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="AuthorizeSandboxDTO", type="object",
 *     properties={
 *          @OA\Property(property="user_entity", ref="#/components/schemas/MerchantUserEntity"),
 *          @OA\Property(property="email", ref="#/components/schemas/TinyText"),
 *          @OA\Property(property="sandbox_merchant_payment_uuid", ref="#/components/schemas/UUID")
 *     }
 * )
 */
class AuthorizeSandboxDTO implements ArrayableInterface
{
    private $userEntity;

    private $email;

    private $sandboxMerchantPaymentUuid;

    public function __construct(MerchantUserEntity $userEntity, string $email, string $sandboxMerchantPaymentUuid)
    {
        $this->userEntity = $userEntity;
        $this->email = $email;
        $this->sandboxMerchantPaymentUuid = $sandboxMerchantPaymentUuid;
    }

    public function getUserEntity(): MerchantUserEntity
    {
        return $this->userEntity;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getSandboxMerchantPaymentUuid(): string
    {
        return $this->sandboxMerchantPaymentUuid;
    }

    public function toArray(): array
    {
        return [
            'user_entity' => $this->userEntity->toArray(),
            'email' => $this->email,
            'sandbox_payment_merchant_uuid' => $this->sandboxMerchantPaymentUuid,
        ];
    }
}
