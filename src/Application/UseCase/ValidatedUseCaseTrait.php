<?php

namespace App\Application\UseCase;

use App\Application\Exception\RequestValidationException;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
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

    public function validateRequest(ValidatedRequestInterface $request, $constrains = null, $groups = null): void
    {
        $validationErrors = $this->validator->validate($request, $constrains, $groups);

        if ($validationErrors->count() === 0) {
            return;
        }

        $addedValidationErrors = [];
        $nonDuplicatedValidationErrors = new ConstraintViolationList();
        foreach ($validationErrors as $validationError) {
            /** @var ConstraintViolationInterface $validationError */
            $errorKey = $validationError->getPropertyPath() . '=' . $validationError->getMessage();
            if (isset($addedValidationErrors[$errorKey])) {
                continue;
            }
            $addedValidationErrors[$errorKey] = true;
            $nonDuplicatedValidationErrors->add($validationError);
        }

        throw new RequestValidationException($nonDuplicatedValidationErrors);
    }
}
