<?php

namespace App\Application\UseCase\GetMerchantNotifications;

use App\Application\UseCase\ValidatedUseCaseInterface;
use App\Application\UseCase\ValidatedUseCaseTrait;
use App\Infrastructure\Repository\DebtorInformationChangeRequestRepository;

class GetMerchantNotificationsUseCase implements ValidatedUseCaseInterface
{
    use ValidatedUseCaseTrait;

    private $changeRequestRepository;

    public function __construct(
        DebtorInformationChangeRequestRepository $changeRequestRepository
    ) {
        $this->changeRequestRepository = $changeRequestRepository;
    }

    public function execute(GetMerchantNotificationsRequest $request): GetMerchantNotificationsResponse
    {
        $count = $this->changeRequestRepository->getNotSeenCountByMerchantId(
            $request->getMerchantId()
        );

        return new GetMerchantNotificationsResponse($count);
    }
}
