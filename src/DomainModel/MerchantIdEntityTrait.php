<?php

namespace App\DomainModel;

trait MerchantIdEntityTrait
{
    private $merchantId;

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    /**
     * @param  int   $merchantId
     * @return $this
     */
    public function setMerchantId(int $merchantId)
    {
        $this->merchantId = $merchantId;

        return $this;
    }
}
