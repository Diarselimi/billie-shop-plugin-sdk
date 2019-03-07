<?php

namespace App\Application\UseCase;

use Symfony\Component\Validator\Validator\ValidatorInterface;

interface ValidatedUseCaseInterface
{
    public function setValidator(ValidatorInterface $validator);
}
