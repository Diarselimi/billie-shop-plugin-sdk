<?php

namespace App\Http\Controller;

use App\Application\Exception\MerchantDebtorNotFoundException;
use App\Application\PaellaCoreCriticalException;
use App\Application\UseCase\UpdateMerchantDebtorCompany\MerchantDebtorUpdateFailedException;
use App\Application\UseCase\UpdateMerchantDebtorCompany\UpdateMerchantDebtorCompanyRequest;
use App\Application\UseCase\UpdateMerchantDebtorCompany\UpdateMerchantDebtorCompanyUseCase;
use Symfony\Component\HttpFoundation\Request;

class UpdateMerchantDebtorCompanyController
{
    private $useCase;

    public function __construct(UpdateMerchantDebtorCompanyUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(Request $request, int $merchantId, string $merchantDebtorExternalId): void
    {
        try {
            $request = (new UpdateMerchantDebtorCompanyRequest())
                ->setMerchantDebtorExternalId($merchantDebtorExternalId)
                ->setMerchantId($merchantId)
                ->setName($request->request->get('name'))
                ->setAddressHouse($request->request->get('address_house'))
                ->setAddressStreet($request->request->get('address_street'))
                ->setAddressCity($request->request->get('address_city'))
                ->setAddressPostalCode($request->request->get('address_postal_code'))
            ;

            $this->useCase->execute($request);
        } catch (MerchantDebtorNotFoundException $exception) {
            throw new PaellaCoreCriticalException(
                'Merchant debtor not found',
                PaellaCoreCriticalException::CODE_NOT_FOUND
            );
        } catch (MerchantDebtorUpdateFailedException $exception) {
            throw new PaellaCoreCriticalException(
                'Company service update failed',
                PaellaCoreCriticalException::CODE_ALFRED_EXCEPTION
            );
        }
    }
}
