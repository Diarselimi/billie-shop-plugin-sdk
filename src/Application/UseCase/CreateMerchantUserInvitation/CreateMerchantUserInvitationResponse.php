<?php

namespace App\Application\UseCase\CreateMerchantUserInvitation;

use App\DomainModel\ArrayableInterface;
use App\DomainModel\MerchantUserInvitation\MerchantUserInvitationEntity;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *      schema="CreateMerchantUserInvitationResponse",
 *      x={"groups": {"private"}},
 *      type="object",
 *      required={"invitation_uuid"},
 *      properties={
 *          @OA\Property(property="invitation_uuid", ref="#/components/schemas/UUID"),
 *      }
 * )
 */
class CreateMerchantUserInvitationResponse implements ArrayableInterface
{
    private $invitation;

    public function __construct(MerchantUserInvitationEntity $invitation)
    {
        $this->invitation = $invitation;
    }

    public function getInvitation(): MerchantUserInvitationEntity
    {
        return $this->invitation;
    }

    public function toArray(): array
    {
        return [
            'invitation_uuid' => $this->getInvitation()->getUuid(),
        ];
    }
}
