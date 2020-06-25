<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetExternalDebtors;

use App\Application\UseCase\ValidatedRequestInterface;
use Symfony\Component\Validator\Constraints as Assert;

class GetExternalDebtorsRequest implements ValidatedRequestInterface
{
    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="int")
     */
    private $merchantId;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="string")
     */
    private $searchString;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="int")
     */
    private $limit;

    public function __construct(
        $merchantId,
        $searchString,
        $limit
    ) {
        $this->merchantId = $merchantId;
        $this->limit = $limit;
        $this->searchString = $searchString;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function getSearchString(): string
    {
        return $this->searchString;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }
}
