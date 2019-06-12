<?php

namespace App\DomainModel\MerchantDebtorResponse;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(schema="MerchantDebtorListItem", title="Debtor Basic Info", allOf={@OA\Schema(ref="#/components/schemas/AbstractMerchantDebtorResponse")})
 */
class MerchantDebtorListItem extends AbstractMerchantDebtor
{
}
