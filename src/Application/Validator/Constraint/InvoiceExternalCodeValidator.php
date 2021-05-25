<?php

namespace App\Application\Validator\Constraint;

use App\DomainModel\Invoice\InvoiceServiceException;
use App\DomainModel\Invoice\InvoiceServiceInterface;
use App\DomainModel\Merchant\MerchantRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class InvoiceExternalCodeValidator extends ConstraintValidator
{
    private InvoiceServiceInterface $invoiceService;

    private MerchantRepositoryInterface $merchantRepository;

    public function __construct(
        InvoiceServiceInterface $invoiceService,
        MerchantRepositoryInterface $merchantRepository
    ) {
        $this->invoiceService = $invoiceService;
        $this->merchantRepository = $merchantRepository;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof InvoiceExternalCode) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\ExternalCode');
        }

        $request = $this->context->getRoot();
        if (!$value || !$request->getMerchantId()) {
            return;
        }

        try {
            $invoices = $this->invoiceService->getByParameters([
                'customer_company_uuid' => $this->merchantRepository->getOneById($request->getMerchantId())->getPaymentUuid(),
                'external_code' => $value,
            ]);
        } catch (InvoiceServiceException $exception) {
            return;
        }

        if ($invoices->isEmpty()) {
            return;
        }

        $this->context->addViolation($constraint->message, ['{{ value }}' => $value]);
    }
}
