<?php

declare(strict_types=1);

namespace App\Support;

use ForceUTF8\Encoding;
use Ozean12\Support\Serialization\StringableInterface;

final class SearchInput implements StringableInterface
{
    public const MAX_SEARCH_STRING_LENGTH = 200;

    private string $value;

    public function __construct(string $value, bool $trim = true, ?int $maxLength = self::MAX_SEARCH_STRING_LENGTH)
    {
        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        $value = Encoding::toUTF8($value);

        if ($trim) {
            $value = trim($value);
        }

        if ($maxLength > 0) {
            $value = mb_substr($value, 0, $maxLength);
        }

        if ($trim) {
            $value = trim($value);
        }

        $this->value = $value;
    }

    public static function asString(
        string $value,
        bool $trim = true,
        ?int $maxLength = self::MAX_SEARCH_STRING_LENGTH
    ): string {
        return (new self($value, $trim, $maxLength))->toString();
    }

    public function __toString()
    {
        return $this->value;
    }

    public function toString(): string
    {
        return $this->value;
    }
}
