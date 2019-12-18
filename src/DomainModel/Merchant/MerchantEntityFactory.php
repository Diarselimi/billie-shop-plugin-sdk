<?php

namespace App\DomainModel\Merchant;

use App\Helper\Uuid\UuidGeneratorInterface;

class MerchantEntityFactory
{
    private $uuidGenerator;

    public function __construct(UuidGeneratorInterface $uuidGenerator)
    {
        $this->uuidGenerator = $uuidGenerator;
    }

    public function createFromDatabaseRow(array $row): MerchantEntity
    {
        return (new MerchantEntity())
            ->setId($row['id'])
            ->setName($row['name'])
            ->setApiKey($row['api_key'])
            ->setFinancingPower($row['financing_power'])
            ->setFinancingLimit($row['available_financing_limit'])
            ->setCompanyId($row['company_id'])
            ->setPaymentUuid($row['payment_merchant_id'])
            ->setSandboxPaymentUuid($row['sandbox_payment_merchant_id'])
            ->setIsActive((bool) $row['is_active'])
            ->setWebhookUrl($row['webhook_url'])
            ->setWebhookAuthorization($row['webhook_authorization'])
            ->setOauthClientId($row['oauth_client_id'])
            ->setCreatedAt(new \DateTime($row['created_at']))
            ->setUpdatedAt(new \DateTime($row['updated_at']))
        ;
    }

    public function createFromMerchantCreationResponse(array $payload): MerchantEntity
    {
        return (new MerchantEntity())
            ->setId($payload['id'])
            ->setName($payload['name'])
            ->setFinancingPower($payload['financing_power'])
            ->setFinancingLimit($payload['financing_limit'])
            ->setApiKey($payload['api_key'])
            ->setCompanyId($payload['company_id'])
            ->setPaymentUuid($payload['payment_merchant_id'])
            ->setIsActive((bool) $payload['is_active'])
            ->setWebhookUrl($payload['webhook_url'])
            ->setWebhookAuthorization($payload['webhook_authorization'])
            ->setOauthClientId($payload['oauth_client_id'])
            ->setCreatedAt(new \DateTime($payload['created_at']))
            ->setUpdatedAt(new \DateTime($payload['updated_at']))
        ;
    }

    public function createFromCreationDTO(MerchantCreationDTO $creationDTO): MerchantEntity
    {
        return (new MerchantEntity())
            ->setCompanyId($creationDTO->getCompany()->getId())
            ->setFinancingPower($creationDTO->getMerchantFinancingLimit())
            ->setFinancingLimit($creationDTO->getMerchantFinancingLimit())
            ->setName($creationDTO->getCompany()->getName())
            ->setApiKey($creationDTO->getApiKey())
            ->setPaymentUuid($creationDTO->getPaymentUuid())
            ->setWebhookUrl($creationDTO->getWebhookUrl())
            ->setWebhookAuthorization($creationDTO->getWebhookAuthorization())
            ->setOauthClientId($creationDTO->getOauthClient()->getClientId())
            ->setIsActive(true)
        ;
    }

    /**
     * @param  array[]          $rows
     * @return MerchantEntity[]
     */
    public function createFromDatabaseRows(array $rows)
    {
        $merchants = [];

        foreach ($rows as $row) {
            $merchants[] = $this->createFromDatabaseRow($row);
        }

        return $merchants;
    }
}
