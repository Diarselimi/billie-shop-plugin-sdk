<?php

namespace App\Http\ApiError;

use App\DomainModel\ArrayableInterface;

class ApiError implements ArrayableInterface, \JsonSerializable
{
    public const CODE_OPERATION_FAILED = 'operation_failed';

    public const CODE_REQUEST_INVALID = 'request_invalid';

    public const CODE_FORBIDDEN = 'forbidden';

    public const CODE_RESOURCE_NOT_FOUND = 'resource_not_found';

    public const CODE_REQUEST_VALIDATION_ERROR = 'request_validation_error';

    public const CODE_UNAUTHORIZED = 'unauthorized';

    public const CODE_INTERNAL_ERROR = 'internal_error';

    public const CODE_SERVICE_UNAVAILABLE = 'service_unavailable';

    private $title;

    private $code;

    private $source;

    private $additionalData;

    public function __construct(string $title, ?string $code, ?string $source = null, array $additionalData = [])
    {
        foreach ($additionalData as $i => $value) {
            if ($value instanceof ArrayableInterface) {
                $additionalData[$i] = $value = $value->toArray();
            }
        }

        $this->title = $title;
        $this->code = $code;
        $this->source = $source;
        $this->additionalData = $additionalData;
    }

    public function toArray(): array
    {
        $payload = ['title' => $this->title, 'code' => $this->code];

        if ($this->source) {
            $payload['source'] = $this->source;
        }

        return array_merge($payload, $this->additionalData);
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }
}
