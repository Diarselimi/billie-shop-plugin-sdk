<?php

namespace App\UserInterface\Http\KlarnaScheme;

use Symfony\Component\HttpFoundation\JsonResponse;

class KlarnaResponse extends JsonResponse
{
    public function __construct(?array $body)
    {
        parent::__construct($body);
    }

    public static function empty(): self
    {
        return new self(null);
    }

    public static function withErrorMessage(string $message): self
    {
        return new self(['error_messages' => [$message]]);
    }

    public static function withErrorFromException(\Throwable $ex): self
    {
        return self::withErrorMessage($ex->getMessage());
    }
}
