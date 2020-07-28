<?php

declare(strict_types=1);

namespace App\DomainModel\IdentityVerification;

use App\DomainModel\ArrayableInterface;
use App\Support\DateFormat;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="MerchantUserIdentityVerificationResponse",
 *     title="Merchant User Identity Verification Response",
 *     properties={
 *         @OA\Property(property="url", type="string", example="https://postident.deutschepost.de/identportal/?vorgangsnummer=0AAAAAA0AAAA"),
 *         @OA\Property(property="valid_till", type="string", example="2020-01-01 00:00:00"),
 *         @OA\Property(property="case_status", type="string", example="closed"),
 *         @OA\Property(property="identification_status", type="string", example="declined")
 *     }
 * )
 */
class IdentityVerificationCaseDTO implements ArrayableInterface
{
    private $url;

    private $validTill;

    private $caseStatus;

    private $identificationStatus;

    private $isCurrent;

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getValidTill(): \DateTime
    {
        return $this->validTill;
    }

    public function setValidTill(\DateTime $validTill): self
    {
        $this->validTill = $validTill;

        return $this;
    }

    public function getCaseStatus(): string
    {
        return $this->caseStatus;
    }

    public function setCaseStatus(string $caseStatus): self
    {
        $this->caseStatus = $caseStatus;

        return $this;
    }

    public function getIdentificationStatus(): ?string
    {
        return $this->identificationStatus;
    }

    public function setIdentificationStatus(?string $identificationStatus): self
    {
        $this->identificationStatus = $identificationStatus;

        return $this;
    }

    public function isCurrent(): bool
    {
        return $this->isCurrent;
    }

    public function setIsCurrent(bool $isCurrent): self
    {
        $this->isCurrent = $isCurrent;

        return $this;
    }

    public function isValid(): bool
    {
        return $this->isCurrent() && $this->getValidTill() > new \DateTime();
    }

    public function toArray(): array
    {
        return [
            'url' => $this->getUrl(),
            'valid_till' => $this->getValidTill()->format(DateFormat::FORMAT_YMD_HIS),
            'case_status' => $this->getCaseStatus(),
            'identification_status' => $this->getIdentificationStatus(),
        ];
    }
}
