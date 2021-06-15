<?php

namespace App\DomainModel\Merchant;

use App\Support\AbstractFactory;
use App\Support\TwoWayEncryption\Encryptor;
use Ozean12\Money\Money;

class MerchantEntityFactory extends AbstractFactory
{
    private $defaultInvestorUuid;

    private Encryptor $encryptor;

    public function __construct(string $investorUuid, Encryptor $encryptor)
    {
        $this->defaultInvestorUuid = $investorUuid;
        $this->encryptor = $encryptor;
    }

    public function createFromArray(array $data): MerchantEntity
    {
        try {
            $plainApiKey = $this->encryptor->decrypt($data['api_key']);
        } catch (\Exception $e) {
            $plainApiKey = '';
        }

        return (new MerchantEntity())
            ->setId($data['id'])
            ->setName($data['name'])
            ->setCompanyUuid($data['company_uuid'])
            ->setApiKey($plainApiKey)
            ->setFinancingPower(new Money($data['financing_power']))
            ->setFinancingLimit(new Money($data['available_financing_limit']))
            ->setCompanyId($data['company_id'])
            ->setPaymentUuid($data['payment_merchant_id'])
            ->setSepaB2BDocumentUuid($data['sepa_b2b_document_uuid'])
            ->setIsActive((bool) $data['is_active'])
            ->setWebhookUrl($data['webhook_url'])
            ->setWebhookAuthorization($data['webhook_authorization'])
            ->setOauthClientId($data['oauth_client_id'])
            ->setSandboxPaymentUuid($data['sandbox_payment_merchant_id'])
            ->setInvestorUuid($data['investor_uuid'])
            ->setCreatedAt(new \DateTime($data['created_at']))
            ->setUpdatedAt(new \DateTime($data['updated_at']))
        ;
    }

    public function createFromCreationDTO(MerchantCreationDTO $creationDTO): MerchantEntity
    {
        return (new MerchantEntity())
            ->setCompanyId($creationDTO->getCompany()->getId())
            ->setCompanyUuid($creationDTO->getCompany()->getUuid())
            ->setFinancingPower(new Money($creationDTO->getMerchantFinancingLimit()))
            ->setFinancingLimit(new Money($creationDTO->getMerchantFinancingLimit()))
            ->setName($creationDTO->getCompany()->getName())
            ->setApiKey($creationDTO->getApiKey())
            ->setPaymentUuid($creationDTO->getPaymentUuid())
            ->setWebhookUrl($creationDTO->getWebhookUrl())
            ->setWebhookAuthorization($creationDTO->getWebhookAuthorization())
            ->setOauthClientId($creationDTO->getOauthClient()->getClientId())
            ->setIsActive(true)
            ->setInvestorUuid($this->defaultInvestorUuid)
        ;
    }

    public function createFromMerchantCreationResponse(array $payload): MerchantEntity
    {
        return (new MerchantEntity())
            ->setId($payload['id'])
            ->setName($payload['name'])
            ->setFinancingPower(new Money($payload['financing_power']))
            ->setFinancingLimit(new Money($payload['financing_limit']))
            ->setApiKey($payload['api_key'])
            ->setCompanyId($payload['company_id'])
            ->setCompanyUuid($payload['company_uuid'])
            ->setPaymentUuid($payload['payment_merchant_id'])
            ->setIsActive((bool) $payload['is_active'])
            ->setWebhookUrl($payload['webhook_url'])
            ->setWebhookAuthorization($payload['webhook_authorization'])
            ->setOauthClientId($payload['oauth_client_id'])
            ->setInvestorUuid($this->defaultInvestorUuid)
            ->setCreatedAt(new \DateTime($payload['created_at']))
            ->setUpdatedAt(new \DateTime($payload['updated_at']))
        ;
    }
}
