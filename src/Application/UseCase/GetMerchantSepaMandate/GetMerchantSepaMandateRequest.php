<?php

namespace App\Application\UseCase\GetMerchantSepaMandate;

class GetMerchantSepaMandateRequest
{
    private $fileUuid;

    public function __construct(string $fileUuid)
    {
        $this->fileUuid = $fileUuid;
    }

    public function getFileUuid(): string
    {
        return $this->fileUuid;
    }
}
