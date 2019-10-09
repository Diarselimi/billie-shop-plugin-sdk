<?php

namespace App\Application\UseCase\GetMerchantUser;

use App\DomainModel\Address\AddressEntity;
use App\DomainModel\ArrayableInterface;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="GetMerchantUserResponse", title="Get Merchant User Response", type="object", properties={
 *      @OA\Property(property="first_name", type="string", nullable=false),
 *      @OA\Property(property="last_name", type="string", nullable=false),
 *      @OA\Property(property="email", type="string", format="email", nullable=false),
 *      @OA\Property(property="user_id", type="integer", nullable=false),
 *      @OA\Property(property="merchant_company", type="object", description="Merchant company data.", properties={
 *          @OA\Property(property="name", ref="#/components/schemas/TinyText", nullable=true, example="Billie GmbH"),
 *          @OA\Property(property="address_house_number", ref="#/components/schemas/TinyText", nullable=true, example="4"),
 *          @OA\Property(property="address_street", ref="#/components/schemas/TinyText", nullable=true, example="Charlottenstr."),
 *          @OA\Property(property="address_city", ref="#/components/schemas/TinyText", nullable=true, example="Berlin"),
 *          @OA\Property(property="address_postal_code", type="string", nullable=true, maxLength=5, example="10969"),
 *          @OA\Property(property="address_country", type="string", nullable=true, maxLength=2),
 *      }),
 *      @OA\Property(
 *          property="permissions",
 *          type="array",
 *          nullable=false,
 *          @OA\Items(ref="#/components/schemas/MerchantUserRoles")
 *      )
 * })
 */
class GetMerchantUserResponse implements ArrayableInterface
{
    private $userId;

    private $roles;

    private $firstName;

    private $lastName;

    private $email;

    private $merchantCompanyName;

    private $merchantCompanyAddress;

    public function setUserId(int $userId): GetMerchantUserResponse
    {
        $this->userId = $userId;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): GetMerchantUserResponse
    {
        $this->roles = $roles;

        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): GetMerchantUserResponse
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): GetMerchantUserResponse
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getMerchantCompanyName(): string
    {
        return $this->merchantCompanyName;
    }

    public function setMerchantCompanyName(string $merchantCompanyName): GetMerchantUserResponse
    {
        $this->merchantCompanyName = $merchantCompanyName;

        return $this;
    }

    public function getMerchantCompanyAddress(): AddressEntity
    {
        return $this->merchantCompanyAddress;
    }

    public function setMerchantCompanyAddress(AddressEntity $merchantCompanyAddress): GetMerchantUserResponse
    {
        $this->merchantCompanyAddress = $merchantCompanyAddress;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): GetMerchantUserResponse
    {
        $this->email = $email;

        return $this;
    }

    public function toArray(): array
    {
        $address = $this->getMerchantCompanyAddress();

        return [
            'user_id' => $this->getUserId(),
            'first_name' => $this->getFirstName(),
            'last_name' => $this->getLastName(),
            'email' => $this->getEmail(),
            'merchant_company' => [
                'name' => $this->getMerchantCompanyName(),
                'address_house_number' => $address->getHouseNumber(),
                'address_street' => $address->getStreet(),
                'address_city' => $address->getCity(),
                'address_postal_code' => $address->getPostalCode(),
                'address_country' => $address->getCountry(),
            ],
            'permissions' => $this->getRoles(),
        ];
    }
}
