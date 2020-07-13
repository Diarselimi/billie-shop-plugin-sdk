<?php

declare(strict_types=1);

namespace App\DomainModel\MerchantDebtor;

use App\Infrastructure\Graphql\AbstractSearchGraphQLDTO;

class SearchMerchantDebtorsDTO extends AbstractSearchGraphQLDTO
{
    private $merchantId;

    private $changeRequestStates;

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function setMerchantId(int $merchantId): self
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    public function getChangeRequestStates(): string
    {
        return $this->changeRequestStates;
    }

    public function setChangeRequestStates(string $states): self
    {
        $this->changeRequestStates = $states;

        return $this;
    }
}
