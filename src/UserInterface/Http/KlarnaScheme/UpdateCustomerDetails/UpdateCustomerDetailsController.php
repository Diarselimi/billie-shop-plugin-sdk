<?php

namespace App\UserInterface\Http\KlarnaScheme\UpdateCustomerDetails;

use App\UserInterface\Http\KlarnaScheme\KlarnaResponse;

class UpdateCustomerDetailsController
{
    public function execute(): KlarnaResponse
    {
        return KlarnaResponse::withErrorMessage('Not supported');
    }
}
