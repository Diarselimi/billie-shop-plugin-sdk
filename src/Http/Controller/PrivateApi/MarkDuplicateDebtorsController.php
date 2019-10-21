<?php

namespace App\Http\Controller\PrivateApi;

use App\Application\UseCase\MarkDuplicateDebtor\MarkDuplicateDebtorRequest;
use App\Application\UseCase\MarkDuplicateDebtor\MarkDuplicateDebtorUseCase;
use Billie\MonitoringBundle\Service\Logging\LoggingInterface;
use Billie\MonitoringBundle\Service\Logging\LoggingTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use OpenApi\Annotations as OA;

/**
 * @OA\Post(
 *     path="/merchant-debtor/mark-duplicates",
 *     operationId="mark_duplicate_debtors",
 *     summary="Mark Merchant Debtor Duplicates",
 *
 *     tags={"Support"},
 *     x={"groups":{"private"}},
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(type="object", properties={
 *              @OA\Property(
 *                  property="duplicates",
 *                  type="array",
 *                  nullable=false,
 *                  @OA\Items(ref="#/components/schemas/MarkDuplicateDebtorItem")
 *              )
 *          }))
 *     ),
 *
 *     @OA\Response(response=204, description="Merchant debtor is marked as duplicate"),
 *     @OA\Response(response=404, ref="#/components/responses/NotFound"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class MarkDuplicateDebtorsController implements LoggingInterface
{
    use LoggingTrait;

    private $useCase;

    public function __construct(MarkDuplicateDebtorUseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function execute(Request $request): void
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
    }
}
