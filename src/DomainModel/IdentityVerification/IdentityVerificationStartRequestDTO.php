<?php

declare(strict_types=1);

namespace App\DomainModel\IdentityVerification;

use App\DomainModel\ArrayableInterface;

class IdentityVerificationStartRequestDTO implements ArrayableInterface
{
    private $firstName;

    private $lastName;

    private $email;

    private $redirectUrlCouponRequested;

    private $redirectUrlReviewPending;

    private $redirectUrlDeclined;

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): IdentityVerificationStartRequestDTO
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): IdentityVerificationStartRequestDTO
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): IdentityVerificationStartRequestDTO
    {
        $this->email = $email;

        return $this;
    }

    public function getRedirectUrlCouponRequested(): string
    {
        return $this->redirectUrlCouponRequested;
    }

    public function setRedirectUrlCouponRequested($redirectUrlCouponRequested): IdentityVerificationStartRequestDTO
    {
        $this->redirectUrlCouponRequested = $redirectUrlCouponRequested;

        return $this;
    }

    public function getRedirectUrlReviewPending(): string
    {
        return $this->redirectUrlReviewPending;
    }

    public function setRedirectUrlReviewPending($redirectUrlReviewPending): IdentityVerificationStartRequestDTO
    {
        $this->redirectUrlReviewPending = $redirectUrlReviewPending;

        return $this;
    }

    public function getRedirectUrlDeclined(): string
    {
        return $this->redirectUrlDeclined;
    }

    public function setRedirectUrlDeclined($redirectUrlDeclined): IdentityVerificationStartRequestDTO
    {
        $this->redirectUrlDeclined = $redirectUrlDeclined;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'first_name' => $this->getFirstName(),
            'last_name' => $this->getLastName(),
            'email' => $this->getEmail(),
            'redirect_url_coupon_requested' => $this->getRedirectUrlCouponRequested(),
            'redirect_url_review_pending' => $this->getRedirectUrlReviewPending(),
            'redirect_url_declined' => $this->getRedirectUrlDeclined(),
        ];
    }
}
