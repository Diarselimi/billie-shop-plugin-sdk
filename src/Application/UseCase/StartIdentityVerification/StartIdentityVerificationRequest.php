<?php

declare(strict_types=1);

namespace App\Application\UseCase\StartIdentityVerification;

use App\Application\UseCase\ValidatedRequestInterface;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(
 *      schema="StartIdentityVerificationRequest",
 *      type="object",
 *      properties={
 *          @OA\Property(property="redirect_url_coupon_requested", type="string", format="url"),
 *          @OA\Property(property="redirect_url_review_pending", type="string", format="url"),
 *          @OA\Property(property="redirect_url_declined", type="string", format="url"),
 *      },
 *      required={"redirect_url_coupon_requested", "redirect_url_review_pending", "redirect_url_declined"}
 * )
 */
class StartIdentityVerificationRequest implements ValidatedRequestInterface
{
    private $merchantId;

    private $merchantPaymentUuid;

    private $merchantUserId;

    private $email;

    private $firstName;

    private $lastName;

    /**
     * @Assert\NotBlank()
     * @Assert\Url()
     */
    private $redirectUrlCouponRequested;

    /**
     * @Assert\NotBlank()
     * @Assert\Url()
     */
    private $redirectUrlReviewPending;

    /**
     * @Assert\NotBlank()
     * @Assert\Url()
     */
    private $redirectUrlDeclined;

    public function __construct(int $merchantId, string $merchantPaymentUuid)
    {
        $this->merchantId = $merchantId;
        $this->merchantPaymentUuid = $merchantPaymentUuid;
    }

    public function getMerchantId(): int
    {
        return $this->merchantId;
    }

    public function getMerchantPaymentUuid(): string
    {
        return $this->merchantPaymentUuid;
    }

    public function getMerchantUserId(): int
    {
        return $this->merchantUserId;
    }

    public function setMerchantUserId(int $merchantUserId): StartIdentityVerificationRequest
    {
        $this->merchantUserId = $merchantUserId;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): StartIdentityVerificationRequest
    {
        $this->email = $email;

        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): StartIdentityVerificationRequest
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): StartIdentityVerificationRequest
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getRedirectUrlCouponRequested(): string
    {
        return $this->redirectUrlCouponRequested;
    }

    public function setRedirectUrlCouponRequested($redirectUrlCouponRequested): StartIdentityVerificationRequest
    {
        $this->redirectUrlCouponRequested = $redirectUrlCouponRequested;

        return $this;
    }

    public function getRedirectUrlReviewPending(): string
    {
        return $this->redirectUrlReviewPending;
    }

    public function setRedirectUrlReviewPending($redirectUrlReviewPending): StartIdentityVerificationRequest
    {
        $this->redirectUrlReviewPending = $redirectUrlReviewPending;

        return $this;
    }

    public function getRedirectUrlDeclined(): string
    {
        return $this->redirectUrlDeclined;
    }

    public function setRedirectUrlDeclined($redirectUrlDeclined): StartIdentityVerificationRequest
    {
        $this->redirectUrlDeclined = $redirectUrlDeclined;

        return $this;
    }
}
