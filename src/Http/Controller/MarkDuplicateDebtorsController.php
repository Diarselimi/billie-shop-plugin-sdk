<?php

namespace App\Http\Controller;

use App\Application\UseCase\MarkDuplicateDebtor\MarkDuplicateDebtorRequest;
use App\Application\UseCase\MarkDuplicateDebtor\MarkDuplicateDebtorUseCase;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class MarkDuplicateDebtorsController implements LoggingInterface
{
    use LoggingTrait;

    private $useCase;

    public function __construct(MarkDuplicateDebtorUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(Request $request): JsonResponse
    {
        $duplicates = $request->request->get('duplicates', []);

        if (is_string($duplicates)) {
            $duplicates = json_decode($duplicates, true);
        }

        if (!is_array($duplicates) || empty($duplicates) || json_last_error() > 0) {
            throw new BadRequestHttpException($duplicates);
        }

        $requests = [];
        foreach ($duplicates as $duplicate) {
            $requests[] = new MarkDuplicateDebtorRequest((int) $duplicate['debtor_id'], (int) $duplicate['is_duplicate_of']);
        }

        $this->useCase->execute(... $requests);

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
