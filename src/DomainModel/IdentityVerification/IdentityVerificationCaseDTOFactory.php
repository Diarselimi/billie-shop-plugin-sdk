<?php

declare(strict_types=1);

namespace App\DomainModel\IdentityVerification;

use App\Support\AbstractFactory;

class IdentityVerificationCaseDTOFactory extends AbstractFactory
{
    public function createFromArray(array $data): IdentityVerificationCaseDTO
    {
        return (new IdentityVerificationCaseDTO())
            ->setUrl($data['url'])
            ->setValidTill(new \DateTime($data['valid_till']))
            ->setCaseStatus($data['case_status'])
            ->setIdentificationStatus($data['identification_status'])
            ->setIsCurrent((bool) $data['is_current'])
            ;
    }
}
