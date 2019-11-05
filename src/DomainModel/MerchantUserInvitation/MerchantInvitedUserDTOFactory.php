<?php

namespace App\DomainModel\MerchantUserInvitation;

use App\Support\AbstractFactory;

class MerchantInvitedUserDTOFactory extends AbstractFactory
{
    public function createFromArray(array $row): MerchantInvitedUserDTO
    {
        $user = new MerchantInvitedUserDTO();

        if ($row['merchant_user_id']) {
            $user->setId($row['merchant_user_id']);
        }

        return $user
            ->setUserId($row['merchant_user_uuid'] ?: null)
            ->setMerchantId($row['merchant_id'])
            ->setRoleId($row['merchant_user_role_id'])
            ->setFirstName($row['first_name'] ?: null)
            ->setLastName($row['last_name'] ?: null)
            ->setEmail($row['invitation_email'] ?: null)
            ->setCreatedAt(new \DateTime($row['invitation_created_at']))
            ->setInvitationUuid($row['invitation_uuid'] ?: null)
            ->setInvitationStatus($row['invitation_status'] ? explode(',', $row['invitation_status'])[1] : null);
    }
}
