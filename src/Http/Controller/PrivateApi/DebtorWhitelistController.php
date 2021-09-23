<?php

namespace App\Http\Controller\PrivateApi;

use App\Application\CommandBus;
use App\Application\Exception\CompanyNotFoundException;
use App\Application\UseCase\UpdateDebtorWhitelist\UpdateDebtorWhitelistRequest;
use Symfony\Component\HttpFoundation\Request;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @OA\Post(
 *     path="/debtors/{companyUuid}/whitelist",
 *     operationId="whitelist_debtor",
 *     summary="Whitelist Debtor",
 *
 *     tags={"Support"},
 *     x={"groups":{"private"}},
 *
 *     @OA\Parameter(in="path", name="companyUuid", description="Company UUID", @OA\Schema(ref="#/components/schemas/UUID"), required=true),
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(type="object", properties={
 *              @OA\Property(property="is_whitelisted", type="boolean", nullable=false)
 *          }))
 *     ),
 *
 *     @OA\Response(response=204, description="Debtor is_whitelisted flag is saved."),
 *     @OA\Response(response=404, description="Company not found."),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class DebtorWhitelistController
{
    private CommandBus $commandBus;

    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function execute(Request $request, string $companyUuid): void
    {
        try {
            $this->commandBus->process(
                new UpdateDebtorWhitelistRequest(
                    $companyUuid,
                    $request->request->get('is_whitelisted')
                )
            );
        } catch (CompanyNotFoundException $e) {
            throw new NotFoundHttpException('Company not found', $e);
        }
    }
}
