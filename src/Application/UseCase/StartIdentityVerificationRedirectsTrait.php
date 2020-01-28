<?php

declare(strict_types=1);

namespace App\Application\UseCase;

use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @OA\Schema(
 *      schema="StartIdentityVerificationRedirects",
 *      type="object",
 *      properties={
 *          @OA\Property(property="redirect_url_coupon_requested", type="string", format="url"),
 *          @OA\Property(property="redirect_url_review_pending", type="string", format="url"),
 *          @OA\Property(property="redirect_url_declined", type="string", format="url"),
 *      },
 *      required={"redirect_url_coupon_requested", "redirect_url_review_pending", "redirect_url_declined"}
 * )
 */
trait StartIdentityVerificationRedirectsTrait
{
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

    public function getRedirectUrlCouponRequested(): string
    {
        return $this->redirectUrlCouponRequested;
    }

    public function setRedirectUrlCouponRequested($redirectUrlCouponRequested)
    {
        $this->redirectUrlCouponRequested = $redirectUrlCouponRequested;

        return $this;
    }

    public function getRedirectUrlReviewPending(): string
    {
        return $this->redirectUrlReviewPending;
    }

    public function setRedirectUrlReviewPending($redirectUrlReviewPending)
    {
        $this->redirectUrlReviewPending = $redirectUrlReviewPending;

        return $this;
    }

    public function getRedirectUrlDeclined(): string
    {
        return $this->redirectUrlDeclined;
    }

    public function setRedirectUrlDeclined($redirectUrlDeclined)
    {
        $this->redirectUrlDeclined = $redirectUrlDeclined;

        return $this;
    }
}
