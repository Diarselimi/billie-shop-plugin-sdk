<?php

namespace App\Http\Controller\PublicApiV2;

use App\Application\CommandBus;
use App\Application\Exception\RequestValidationException;
use App\Application\UseCase\InitiateCheckoutSession\InitiateCheckoutSession;
use App\Http\HttpConstantsInterface;
use App\Infrastructure\UuidGeneration\UuidGenerator;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @IsGranted("ROLE_AUTHENTICATED_AS_MERCHANT")
 * @OA\Post(
 *     path="/checkout-sessions",
 *     operationId="checkout_session_create_v2",
 *     summary="Checkout Session Create",
 *     security={{"oauth2"={}}},
 *
 *     tags={"Checkout Server"},
 *     x={"groups":{"publicV2"}},
 *
 *     @OA\RequestBody(
 *          required=true,
 *          @OA\MediaType(mediaType="application/json",
 *          @OA\Schema(type="object", properties={
 *              @OA\Property(property="merchant_customer_id", ref="#/components/schemas/TinyText", description="Debtor ID in the merchant system.")
 *          }))
 *     ),
 *
 *     @OA\Response(response=200, description="Checkout session created.", @OA\JsonContent(
 *          type="object",
 *          required={"id"},
 *          properties={
 *              @OA\Property(property="id", ref="#/components/schemas/UUID", description="Checkout Session Token")
 *          }
 *     )),
 *     @OA\Response(response=400, ref="#/components/responses/BadRequest"),
 *     @OA\Response(response=401, ref="#/components/responses/Unauthorized"),
 *     @OA\Response(response=500, ref="#/components/responses/ServerError")
 * )
 */
class CheckoutCreateSessionController
{
    private UuidGenerator $uuidGenerator;

    private CommandBus $bus;

    public function __construct(
        UuidGenerator $uuidGenerator,
        CommandBus $bus
    ) {
        $this->uuidGenerator = $uuidGenerator;
        $this->bus = $bus;
    }

    public function execute(Request $request): JsonResponse
    {
        $merchantId = $request->attributes->getInt(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID);
        $externalReference = $request->request->get('merchant_customer_id');

        if ($merchantId === null) {
            $this->throwBlankedFieldException(HttpConstantsInterface::REQUEST_ATTRIBUTE_MERCHANT_ID);
        }

        if ($externalReference === null) {
            $this->throwBlankedFieldException('merchant_customer_id');
        }

        $command = new InitiateCheckoutSession(
            $this->uuidGenerator->generate(),
            'DE',
            $merchantId,
            $externalReference
        );

        $this->bus->process($command);

        return new JsonResponse(['id' => (string) $command->token()]);
    }

    private function throwBlankedFieldException(string $field): void
    {
        throw RequestValidationException::createForInvalidValue('This value should not be blank.', $field, null);
    }
}
