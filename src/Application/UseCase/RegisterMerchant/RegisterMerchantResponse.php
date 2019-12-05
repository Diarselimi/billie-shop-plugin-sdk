<?php

namespace App\Application\UseCase\RegisterMerchant;

use App\DomainModel\ArrayableInterface;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationEntity;

/**
 * @OA\Schema(
 *      schema="RegisterMerchantResponse",
 *      type="object",
 *      properties={
 *          @OA\Property(property="uuid", ref="#/components/schemas/UUID"),
 *          @OA\Property(property="name", ref="#/components/schemas/TinyText"),
 *          @OA\Property(property="invitation_token", ref="#/components/schemas/TinyText"),
 *      }
 * )
 */
class RegisterMerchantResponse implements ArrayableInterface
{
    private $merchant;

    private $invitation;

    public function __construct(MerchantEntity $merchant, MerchantUserInvitationEntity $invitation)
    {
        $this->merchant = $merchant;
        $this->invitation = $invitation;
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->merchant->getPaymentUuid(),
            'name' => $this->merchant->getName(),
            'invitation_token' => $this->invitation->getToken(),
        ];
    }
}
