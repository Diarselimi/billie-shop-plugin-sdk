<?php

declare(strict_types=1);

namespace App\Application\UseCase\GetMerchantSepaMandate;

use App\DomainModel\FileService\FileServiceDownloadResponse;
use App\DomainModel\FileService\FileServiceInterface;

class GetMerchantSepaMandateUseCase
{
    private FileServiceInterface $fileService;

    public function __construct(FileServiceInterface $fileService)
    {
        $this->fileService = $fileService;
    }

    public function execute(GetMerchantSepaMandateRequest $request): FileServiceDownloadResponse
    {
        return $this->fileService->download($request->getFileUuid());
    }
}
