<?php

namespace App\Application\UseCase\GetMerchantDebtor;

use Symfony\Component\Validator\Constraints as Assert;

class GetMerchantDebtorPublicRequest extends GetMerchantDebtorRequest
{
    /**
     * @Assert\Type(type="integer")
     * @Assert\NotBlank()
     * @Assert\GreaterThan(0)
     * @var int
     */
    protected $merchantId;

    /**
     * @Assert\Uuid()
     * @Assert\NotBlank()
     * @var string
     */
    protected $merchantDebtorUuid;

    public function __construct(int $merchantId, string $merchantDebtorUuid)
    {
        $this->merchantId = $merchantId;
        $this->merchantDebtorUuid = $merchantDebtorUuid;
        parent::__construct($merchantId, $merchantDebtorUuid);
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }
}
