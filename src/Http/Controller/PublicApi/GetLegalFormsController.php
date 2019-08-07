<?php

namespace App\Http\Controller\PublicApi;

use Symfony\Component\HttpFoundation\JsonResponse;
use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     path="/legal-forms",
 *     operationId="get_legal_forms",
 *     summary="Get Legal Forms",
 *     security={},
 *
 *     tags={"Misc."},
 *     x={"groups":{"checkout-client"}},
 *
 *     @OA\Response(response=200, @OA\JsonContent(type="object", properties={
 *          @OA\Property(property="items", type="array", @OA\Items(
 *            type="object",
 *            properties={
 *               @OA\Property(property="code", type="integer", description="Legal form code", example="90101"),
 *               @OA\Property(property="name", type="string", nullable=true, description="Legal form name", example="AG & Co. KG"),
 *               @OA\Property(
 *                  property="required_input",
 *                  type="string",
 *                  description="Required input (HR-NR => Registration Number, Ust-ID => Registration ID)",
 *                  example="HR-NR"
 *               ),
 *               @OA\Property(property="required", type="boolean", description="Required"),
 *            }
 *        ))
 *     }), description="List of supported legal forms"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class GetLegalFormsController
{
    public function execute(): JsonResponse
    {
        return new JsonResponse(file_get_contents(__DIR__ . '/../../../Resources/legal_forms.json'), 200, [], true);
    }
}
