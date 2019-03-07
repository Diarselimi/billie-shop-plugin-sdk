<?php

namespace App\Application\UseCase;

use App\Application\Exception\RequestValidationException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

trait ValidatedUseCaseTrait
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    public function setValidator(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    public function validateRequest(ValidatedRequestInterface $request): void
    {
        $validationErrors = $this->validator->validate($request);

        if ($validationErrors->count() === 0) {
            return;
        }

        throw new RequestValidationException($validationErrors);
    }
}
