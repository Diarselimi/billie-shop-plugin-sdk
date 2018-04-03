<?php

namespace App\Http\EventSubscriber;

use App\Application\PaellaCoreCriticalException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class JsonConverterSubscriber implements EventSubscriberInterface
{
    public function onRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if (!in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_PATCH])
            || !$request->headers->has('Content-Type')
            || !$request->headers->get('Content-Type') === 'application/json'
            || !$request->getContent()
        ) {
            return;
        }

        $json = $request->getContent();
        $requestData = json_decode($json, true);

        if (is_null($requestData)) {
            throw new PaellaCoreCriticalException("Request couldn't be decoded");
        }

        $request->request->add($requestData);
    }

    public function onView(GetResponseForControllerResultEvent $event)
    {
        return;
        $response = $event->getControllerResult();
        if (!$response || !$response instanceof OrderResponse) {
            return;
        }

        $invoice = null;
        if (!is_null($response->getInvoiceNumber())) {
            $invoice = [
                'invoice_number' => $response->getInvoiceNumber(),
                'payout_amount' => $response->getInvoicePayoutAmount(),
                'fee_amount' => $response->getInvoiceFeeAmount(),
                'fee_rate' => $response->getInvoiceFeeRate(),
                'due_date' => $response->getInvoiceDueDate(),
            ];
        }

        $response = [
            'order_id' => $response->getOrderId(),
            'state' => $response->getState(),
            'bank_account' => ['iban' => $response->getBankAccountIban(), 'bic' => $response->getBankAccountBic()],
            'debtor_company' => [
                'name' => $response->getDebtorCompanyName(),
                'address_house_number' => $response->getDebtorCompanyAddressHouseNumber(),
                'address_street' => $response->getDebtorCompanyAddressStreet(),
                'address_city' => $response->getDebtorCompanyAddressCity(),
                'address_postal_code' => $response->getDebtorCompanyAddressPostalCode(),
                'address_country' => $response->getDebtorCompanyAddressCountry(),
            ],
            'reasons' => $response->getReasons(),
            'invoice' => $invoice,
        ];

        $event->setResponse(new JsonResponse($response));
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'onRequest',
            KernelEvents::VIEW => 'onView',
        ];
    }
}
