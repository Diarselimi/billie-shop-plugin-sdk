<?php

namespace App\DomainModel\DebtorInformationChangeRequest;

use App\DomainModel\ArrayableInterface;
use Billie\PdoBundle\DomainModel\AbstractTimestampableEntity;
use Billie\PdoBundle\DomainModel\StatefulEntity\StatefulEntityInterface;
use Billie\PdoBundle\DomainModel\StatefulEntity\StatefulEntityTrait;
use Billie\PdoBundle\DomainModel\UuidEntityTrait;

class DebtorInformationChangeRequestEntity extends AbstractTimestampableEntity implements ArrayableInterface, StatefulEntityInterface
{
    use UuidEntityTrait, StatefulEntityTrait;

    public const STATE_NEW = 'new';

    public const STATE_PENDING = 'confirmation_pending';

    public const STATE_COMPLETE = 'complete';

    public const STATE_DECLINED = 'declined';

    public const STATE_CANCELED = 'canceled';

    public const INITIAL_STATE = self::STATE_NEW;

    public const ALL_STATES = [self::STATE_NEW, self::STATE_PENDING, self::STATE_COMPLETE, self::STATE_DECLINED, self::STATE_CANCELED];

    private const STATE_TRANSITION_ENTITY_CLASS = DebtorInformationChangeRequestTransitionEntity::class;

    private $companyUuid;

    private $name;

    private $city;

    private $postalCode;

    private $street;

    private $houseNumber;

    private $merchantUserId;

    private $isSeen;

    public function getCompanyUuid(): string
    {
        return $this->companyUuid;
    }

    public function setCompanyUuid(string $companyUuid): DebtorInformationChangeRequestEntity
    {
        $this->companyUuid = $companyUuid;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): DebtorInformationChangeRequestEntity
    {
        $this->name = $name;

        return $this;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function setCity(string $city): DebtorInformationChangeRequestEntity
    {
        $this->city = $city;

        return $this;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function setPostalCode(string $postalCode): DebtorInformationChangeRequestEntity
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getStreet(): string
    {
        return $this->street;
    }

    public function setStreet(string $street): DebtorInformationChangeRequestEntity
    {
        $this->street = $street;

        return $this;
    }

    public function getHouseNumber(): ?string
    {
        return $this->houseNumber;
    }

    public function setHouseNumber(?string $houseNumber): DebtorInformationChangeRequestEntity
    {
        $this->houseNumber = $houseNumber;

        return $this;
    }

    public function getMerchantUserId(): int
    {
        return $this->merchantUserId;
    }

    public function setMerchantUserId(int $merchantUserId): DebtorInformationChangeRequestEntity
    {
        $this->merchantUserId = $merchantUserId;

        return $this;
    }

    public function isSeen(): bool
    {
        return $this->isSeen;
    }

    public function setIsSeen(bool $isSeen): DebtorInformationChangeRequestEntity
    {
        $this->isSeen = $isSeen;

        return $this;
    }

    public function toArray(array $properties = []): array
    {
        $result = [
            'id' => $this->getId(),
            'uuid' => $this->uuid,
            'company_uuid' => $this->companyUuid,
            'name' => $this->name,
            'city' => $this->city,
            'postal_code' => $this->postalCode,
            'street' => $this->street,
            'house_number' => $this->houseNumber,
            'merchant_user_id' => $this->merchantUserId,
            'is_seen' => $this->isSeen,
            'state' => $this->state,
            'created_at' => $this->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $this->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];

        if ($properties) {
            return array_intersect_key($result, array_flip($properties));
        }

        return $result;
    }

    public function getStateTransitionEntityClass(): string
    {
        return self::STATE_TRANSITION_ENTITY_CLASS;
    }
}
