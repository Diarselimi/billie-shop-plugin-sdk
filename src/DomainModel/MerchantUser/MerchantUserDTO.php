<?php

namespace App\DomainModel\MerchantUser;

use App\DomainModel\Address\AddressEntity;
use App\DomainModel\ArrayableInterface;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="MerchantUserDTO", title="Merchant User", type="object", properties={
 *      @OA\Property(property="uuid", nullable=false, ref="#/components/schemas/UUID"),
 *      @OA\Property(property="first_name", type="string", nullable=false),
 *      @OA\Property(property="last_name", type="string", nullable=false),
 *      @OA\Property(property="email", type="string", format="email", nullable=false),
 *      @OA\Property(
 *          property="permissions",
 *          type="array",
 *          nullable=false,
 *          @OA\Items(ref="#/components/schemas/MerchantUserPermissions")
 *      ),
 *      @OA\Property(property="role", type="object", properties={
 *          @OA\Property(property="uuid", ref="#/components/schemas/UUID"),
 *          @OA\Property(property="name", ref="#/components/schemas/TinyText", example="Support", description="Role internal name"),
 *      }),
 *      @OA\Property(property="merchant_company", type="object", description="Merchant the user is logged in to", properties={
 *          @OA\Property(property="name", ref="#/components/schemas/TinyText", nullable=true, example="Billie GmbH"),
 *          @OA\Property(property="address_house_number", ref="#/components/schemas/TinyText", nullable=true, example="4"),
 *          @OA\Property(property="address_street", ref="#/components/schemas/TinyText", nullable=true, example="Charlottenstr."),
 *          @OA\Property(property="address_city", ref="#/components/schemas/TinyText", nullable=true, example="Berlin"),
 *          @OA\Property(property="address_postal_code", type="string", nullable=true, maxLength=5, example="10969"),
 *          @OA\Property(property="address_country", type="string", nullable=true, maxLength=2),
 *      }),
 *     @OA\Property(property="tracking_id", nullable=false, type="integer", description="ID used for tracking the user for support.")
 * })
 */
class MerchantUserDTO implements ArrayableInterface
{
    private $user;

    private $email;

    private $role;

    private $merchantCompanyName;

    private $merchantCompanyAddress;

    public function __construct(MerchantUserEntity $user, string $email, MerchantUserRoleEntity $role)
    {
        $this->user = $user;
        $this->email = $email;
        $this->role = $role;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getRole(): MerchantUserRoleEntity
    {
        return $this->role;
    }

    public function getUser(): MerchantUserEntity
    {
        return $this->user;
    }

    public function getMerchantCompanyName(): string
    {
        return $this->merchantCompanyName;
    }

    public function setMerchantCompanyName(string $merchantCompanyName): MerchantUserDTO
    {
        $this->merchantCompanyName = $merchantCompanyName;

        return $this;
    }

    public function getMerchantCompanyAddress(): AddressEntity
    {
        return $this->merchantCompanyAddress;
    }

    public function setMerchantCompanyAddress(AddressEntity $merchantCompanyAddress): MerchantUserDTO
    {
        $this->merchantCompanyAddress = $merchantCompanyAddress;

        return $this;
    }

    public function toArray(): array
    {
        $address = $this->getMerchantCompanyAddress();

        return [
            'uuid' => $this->getUser()->getUuid(),
            'first_name' => $this->getUser()->getFirstName(),
            'last_name' => $this->getUser()->getLastName(),
            'email' => $this->getEmail(),
            'role' => [
                'uuid' => $this->getRole()->getUuid(),
                'name' => $this->getRole()->getName(),
            ],
            'permissions' => $this->getRole()->getPermissions(),
            'merchant_company' => [
                'name' => $this->getMerchantCompanyName(),
                'address_house_number' => $address->getHouseNumber(),
                'address_street' => $address->getStreet(),
                'address_city' => $address->getCity(),
                'address_postal_code' => $address->getPostalCode(),
                'address_country' => $address->getCountry(),
            ],
            'tracking_id' => $this->getUser()->getId(),
        ];
    }
}
