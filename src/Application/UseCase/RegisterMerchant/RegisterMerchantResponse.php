<?php

namespace App\Application\UseCase\RegisterMerchant;

use App\DomainModel\ArrayableInterface;

/**
 * @OA\Schema(
 *      schema="RegisterMerchantResponse",
 *      type="object",
 *      properties={
 *          @OA\Property(property="uuid", ref="#/components/schemas/UUID"),
 *          @OA\Property(property="name", ref="#/components/schemas/TinyText"),
 *          @OA\Property(property="invitation_token", ref="#/components/schemas/TinyText"),
 *      }
 * )
 */
class RegisterMerchantResponse implements ArrayableInterface
{
    public function toArray(): array
    {
        // TODO: implement RegisterMerchantResponse
        return [
            'uuid' => '0dd6686e-b6d5-4d2a-84f1-d43c66970b30',
            'name' => 'Fake Merchant',
            'invitation_token' => 'f50e26cda7c276e179a8482338a6481a',
        ];
    }
}
