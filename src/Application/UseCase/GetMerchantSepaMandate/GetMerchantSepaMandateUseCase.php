<?php

namespace App\Application\UseCase\GetMerchantSepaMandate;

use App\DomainModel\FileService\FileServiceInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GetMerchantSepaMandateUseCase
{
    private $fileService;

    public function __construct(FileServiceInterface $fileService)
    {
        $this->fileService = $fileService;
    }

    public function execute(GetMerchantSepaMandateRequest $request): StreamedResponse
    {
        $response = $this->fileService->download($request->getFileUuid());

        return (new GetMerchantSepaMandateResponse())->stream($response);
    }
}
