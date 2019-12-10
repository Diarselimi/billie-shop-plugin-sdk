<?php

namespace App\Application\UseCase\GetInvitedMerchantUser;

use App\DomainModel\ArrayableInterface;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="GetInvitedMerchantUserResponse", type="object", properties={
 *      @OA\Property(property="email", type="string", format="email", nullable=false),
 *      @OA\Property(property="should_accept_tc", type="boolean", nullable=false)
 * })
 */
class GetInvitedMerchantUserResponse implements ArrayableInterface
{
    private $email;

    private $shouldAcceptTc;

    public function __construct(string $email, bool $shouldAcceptTc)
    {
        $this->email = $email;
        $this->shouldAcceptTc = $shouldAcceptTc;
    }

    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'should_accept_tc' => $this->shouldAcceptTc,
        ];
    }
}
