<?php

declare(strict_types=1);

namespace App\Application\UseCase\StartIdentityVerification;

use App\DomainModel\ArrayableInterface;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *      schema="StartIdentityVerificationResponse",
 *      type="object",
 *      properties={
 *          @OA\Property(property="url", type="string", format="url"),
 *      }
 * )
 */
class StartIdentityVerificationResponse implements ArrayableInterface
{
    private $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function toArray(): array
    {
        return [
            'url' => $this->getUrl(),
        ];
    }
}
