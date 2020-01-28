<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetSignatoryPowerDetails;

class GetSignatoryPowerDetailsUseCase
{
    public function execute(GetSignatoryPowerDetailsRequest $request): GetSignatoryPowerDetailsResponse
    {
        return new GetSignatoryPowerDetailsResponse($request->getMerchantName(), $request->getSignatoryPowerDTO());
    }
}
