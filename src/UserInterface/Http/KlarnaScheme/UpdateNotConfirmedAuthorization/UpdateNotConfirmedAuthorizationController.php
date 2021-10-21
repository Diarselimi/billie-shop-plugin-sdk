<?php

namespace App\UserInterface\Http\KlarnaScheme\UpdateNotConfirmedAuthorization;

use App\UserInterface\Http\KlarnaScheme\KlarnaResponse;

class UpdateNotConfirmedAuthorizationController
{
    public function execute(): KlarnaResponse
    {
        return KlarnaResponse::withErrorMessage('Not supported');
    }
}
