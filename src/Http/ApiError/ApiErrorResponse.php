<?php

namespace App\Http\ApiError;

use LogicException;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @OA\Schema(
 *     schema="ErrorsObject",
 *     title="API Errors",
 *     type="object",
 *     properties={
 *        @OA\Property(property="errors", type="array", @OA\Items(
 *            type="object",
 *            required={"title", "code"},
 *            properties={
 *               @OA\Property(property="title", type="string", description="Error message"),
 *               @OA\Property(property="code", type="string", nullable=true, description="Error code"),
 *               @OA\Property(property="source", type="string", nullable=true, description="Property path where the error occurred, if applicable"),
 *            }
 *        ))
 *     }
 * )
 */
class ApiErrorResponse extends JsonResponse
{
    private $errors;

    public function __construct(array $errors, int $status = 200, array $headers = [])
    {
        $payload = ['errors' => []];

        foreach ($errors as $error) {
            if (!$error instanceof ApiError) {
                throw new LogicException(static::class . ' only supports errors of type ' . ApiError::class);
            }
            $payload['errors'][] = $error->toArray();
        }

        $this->errors = $payload;
        parent::__construct($this->errors, $status, $headers, false);
    }

    /**
     * Errors payload
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
