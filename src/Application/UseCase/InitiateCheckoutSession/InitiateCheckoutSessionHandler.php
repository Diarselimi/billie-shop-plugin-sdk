<?php

namespace App\Application\UseCase\InitiateCheckoutSession;

use App\Application\CommandHandler;
use App\DomainModel\CheckoutSession\CheckoutSession;
use App\DomainModel\CheckoutSession\CheckoutSessionRepository;
use App\DomainModel\Merchant\MerchantEntity;
use App\DomainModel\Merchant\MerchantRepository;
use App\DomainModel\Merchant\PartnerIdentifier;

class InitiateCheckoutSessionHandler implements CommandHandler
{
    private CheckoutSessionRepository $sessionRepository;

    private MerchantRepository $merchantRepository;

    public function __construct(
        CheckoutSessionRepository $sessionRepository,
        MerchantRepository $merchantRepository
    ) {
        $this->sessionRepository = $sessionRepository;
        $this->merchantRepository = $merchantRepository;
    }

    public function execute(InitiateCheckoutSession $command): void
    {
        $merchant = $this->findMerchant($command);

        $this->sessionRepository->save(new CheckoutSession(
            $command->token(),
            $command->country(),
            $merchant->getId(),
            $command->debtorExternalId()
        ));
    }

    private function findMerchant(InitiateCheckoutSession $command): MerchantEntity
    {
        if ($command->isDirectIntegration()) {
            return $this->findOurMerchant($command->merchantId());
        }

        return $this->findPartnerMerchant($command->partnerIdentifier());
    }

    private function findPartnerMerchant(PartnerIdentifier $partnerIdentifier): MerchantEntity
    {
        $merchant = $this->merchantRepository->getByPartnerIdentifier($partnerIdentifier);

        if (null === $merchant) {
            throw MerchantNotFound::forPartner($partnerIdentifier);
        }

        return $merchant;
    }

    private function findOurMerchant(int $merchantId): MerchantEntity
    {
        $merchant = $this->merchantRepository->getOneById($merchantId);

        if (null === $merchant) {
            throw MerchantNotFound::forMerchant($merchantId);
        }

        return $merchant;
    }
}
