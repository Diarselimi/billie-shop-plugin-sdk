<?php

namespace App\Application\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class RequestValidationException extends \RuntimeException
{
    private const MESSAGE = 'request_validation_error';

    protected $validationErrors;

    public function __construct(ConstraintViolationListInterface $validationErrors = null)
    {
        $this->validationErrors = $validationErrors;

        parent::__construct(self::MESSAGE);
    }

    public function getValidationErrors(): ConstraintViolationListInterface
    {
        return $this->validationErrors;
    }
}
