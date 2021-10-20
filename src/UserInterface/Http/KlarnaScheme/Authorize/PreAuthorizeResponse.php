<?php

namespace App\UserInterface\Http\KlarnaScheme\Authorize;

use App\UserInterface\Http\KlarnaScheme\KlarnaResponse;

class PreAuthorizeResponse extends KlarnaResponse
{
    public function __construct(array $requestData)
    {
        parent::__construct([
            'result' => 'user_action_required',
            'payment_method' => [
                'ui' => [
                    'data' => [ // all the mount data for widget should go here
                        'amount' => [
                            'gross' => 5,
                            'net' => 4,
                            'tax' => 1,
                        ],
                        'duration' => 30,
                    ],
                ],
            ],
        ]);
    }
}
