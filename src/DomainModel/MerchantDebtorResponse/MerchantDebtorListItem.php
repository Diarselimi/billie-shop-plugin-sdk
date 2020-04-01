<?php

namespace App\DomainModel\MerchantDebtorResponse;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="MerchantDebtorListItem",
 *     title="Debtor Basic Info",
 *     allOf={@OA\Schema(ref="#/components/schemas/AbstractMerchantDebtorResponse")},
 *     properties={
 *         @OA\Property(
 *             property="debtor_information_change_request_state",
 *             ref="#/components/schemas/DebtorInformationChangeRequestState"
 *         )
 *     }
 * )
 */
class MerchantDebtorListItem extends AbstractMerchantDebtor
{
}
