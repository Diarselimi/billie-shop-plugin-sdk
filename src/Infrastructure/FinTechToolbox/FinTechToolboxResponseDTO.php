<?php

namespace App\Infrastructure\FinTechToolbox;

class FinTechToolboxResponseDTO
{
    private $code;

    private $description;

    private $postalCode;

    private $city;

    private $bankName;

    private $bic;

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): FinTechToolboxResponseDTO
    {
        $this->code = $code;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): FinTechToolboxResponseDTO
    {
        $this->description = $description;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function setPostalCode(?string $postalCode): FinTechToolboxResponseDTO
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): FinTechToolboxResponseDTO
    {
        $this->city = $city;

        return $this;
    }

    public function getBankName(): ?string
    {
        return $this->bankName;
    }

    public function setBankName(?string $bankName): FinTechToolboxResponseDTO
    {
        $this->bankName = $bankName;

        return $this;
    }

    public function getBic(): ?string
    {
        return $this->bic;
    }

    public function setBic(?string $bic): FinTechToolboxResponseDTO
    {
        $this->bic = $bic;

        return $this;
    }
}
