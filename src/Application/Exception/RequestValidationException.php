<?php

namespace App\Application\Exception;

use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class RequestValidationException extends \RuntimeException
{
    private const MESSAGE = 'request_validation_error';

    protected ConstraintViolationListInterface $validationErrors;

    public function __construct(ConstraintViolationListInterface $validationErrors)
    {
        $this->validationErrors = $validationErrors;

        parent::__construct(self::MESSAGE);
    }

    public function getValidationErrors(): ConstraintViolationListInterface
    {
        return $this->validationErrors;
    }

    public static function createForInvalidValue(string $errorMessage, string $propertyPath, $invalidValue): self
    {
        return new self(
            new ConstraintViolationList(
                [new ConstraintViolation($errorMessage, $errorMessage, [], '', $propertyPath, $invalidValue)]
            )
        );
    }
}
