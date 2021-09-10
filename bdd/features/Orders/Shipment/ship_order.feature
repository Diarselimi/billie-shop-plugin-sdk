Feature:
  In order to ship an order
  I want to have an end point to ship my orders And expect empty response

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-Key" header equal to test
    And The following notification settings exist for merchant 1:
      | notification_type | enabled |
      | order_shipped     | 1       |

  Scenario: Order doesn't exist
    When I send a POST request to "/order/ADDDD/ship" with body:
        """
        {
            "invoice_number": "CO123",
            "invoice_url": "http://example.com/invoice/is/here",
            "shipping_document_url": "http://example.com/proove/is/here"
        }
        """
    Then the response status code should be 404
    And the JSON response should be:
        """
        {"errors":[{"title":"Order not found","code":"resource_not_found"}]}
        """


  Scenario: Order not shipped if no external code exists nor provided
    Given I have a created order with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a POST request to "/order/test-order-uuid/ship" with body:
        """
        {
            "invoice_number": "test",
            "invoice_url": "http://example.com/invoice/is/here",
            "shipping_document_url": "http://example.com/proove/is/here"
        }
        """
    Then the response status code should be 400
    And the JSON response should be:
        """
        {
            "errors":[
                {
                    "source":"external_order_id",
                    "title":"This value should not be blank.",
                    "code":"request_validation_error"
                }
            ]
        }
        """

  Scenario: Successful order shipment
    Given I have a created order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    And I get from payments service create ticket response
    And I get from companies service identify match response
    And GraphQL will respond to getMerchantDebtorDetails query
    And I get from volt service good response
    And I get from files service a good response
    And I get from invoice-butler service no invoices response
    And I get from Banco service search bank good response
    And I get from payments service get order details response
    And I get from Sepa service get mandate valid response
    And I get from OAuth service "/resource-tokens" endpoint response with status 200 and body:
    """
    {
      "company_uuid": "c7be46c0-e049-4312-b274-258ec5aeeb70",
      "resource_type": "buyer_portal_ap",
      "created_at": "2021-05-06 13:00:00",
      "token": "sdg340vpl29kx",
      "email": "test@ozean12.com"
    }
    """
    When I send a POST request to "/order/test-order-uuidCO123/ship" with body:
    """
    {
        "invoice_number": "123456A",
        "external_order_id": "1233XYZ",
        "invoice_url": "http://example.com/invoice/is/here",
        "shipping_document_url": "http://example.com/proove/is/here"
    }
    """
    Then the JSON response should be:
    """
    {
      "order_id": "CO123",
      "uuid": "test-order-uuidCO123",
      "state": "shipped",
      "decline_reason": null,
      "reasons": null,
      "amount": 1000,
      "amount_net": 900,
      "amount_tax": 100,
      "unshipped_amount": 0,
      "unshipped_amount_net": 0,
      "unshipped_amount_tax": 0,
      "duration": 30,
      "dunning_status": null,
      "debtor_company": {
        "name": "Test User Company",
        "address_house_number": "10",
        "address_street": "Heinrich-Heine-Platz",
        "address_postal_code": "10179",
        "address_city": "Berlin",
        "address_country": "DE"
      },
      "bank_account": {
        "iban": "DE27500105171416939916",
        "bic": "BICISHERE"
      },
      "debtor_external_data": {
        "merchant_customer_id": "ext_id",
        "name": "test",
        "address_country": "TE",
        "address_city": "testCity",
        "address_postal_code": "test",
        "address_street": "test",
        "address_house": "test",
        "industry_sector": "test"
      },
      "delivery_address": {
        "house_number": "test",
        "street": "test",
        "city": "test",
        "postal_code": "test",
        "country": "TE"
      },
      "billing_address": {
        "house_number": "test",
        "street": "test",
        "city": "test",
        "postal_code": "test",
        "country": "TE"
      },
      "created_at": "2019-05-20T13:00:00+0200",
      "shipped_at": "2021-05-12T22:33:43+0200",
      "debtor_uuid": null,
      "workflow_name": "order_v1",
      "invoices": [],
      "invoice": {
        "outstanding_amount": 1000,
        "pending_merchant_payment_amount": 0,
        "fee_rate": 20,
        "fee_amount": 238,
        "pending_cancellation_amount": 0,
        "invoice_number": "123456A",
        "payout_amount": 762,
        "due_date": "2019-06-19"
      },
      "selected_payment_method": "direct_debit",
      "payment_methods": [
        {
          "type": "bank_transfer",
          "data": {
            "iban": "DE27500105171416939916",
            "bic": "BICISHERE",
            "bank_name": "Mocked Bank Name GmbH"
          }
        },
        {
          "type":"direct_debit",
          "data":{
            "iban":"DE42500105172497563393",
            "bic":"DENTSXXX",
            "bank_name":"Possum Bank",
            "mandate_reference":"YGG6VI5RQ4OR3GJ0",
            "mandate_execution_date":"2020-01-01 00:00:00",
            "creditor_identification":"DE26ZZZ00001981599"
          }
        }
      ]
    }
    """
    And the response status code should be 200
    And the order "CO123" is in state shipped
    And Order notification should exist for order "CO123" with type "order_shipped"
    And the order "CO123" has invoice data
    And queue should contain message with routing key invoice.create_invoice with below data:
    """
    {
      "uuid":"@string@",
      "externalCode":"123456A",
      "orderExternalCode":"CO123",
      "customerUuid":"f2ec4d5e-79f4-40d6-b411-31174b6519ac",
      "debtorCompanyUuid":"c7be46c0-e049-4312-b274-258ec5aeeb70",
      "debtorCompanyName":"Test User Company",
      "paymentDebtorUuid":"test",
      "grossAmount":"100000",
      "netAmount":"90000",
      "feeRate":2000,
      "grossFeeAmount":"23800",
      "netFeeAmount":"20000",
      "taxFeeAmount":"3800",
      "duration":30,
      "billingDate":"@string@",
      "proofOfDeliveryUrl":"@string@",
      "services":["financing","dci"],
      "paymentUuid":"@string@"
    }
    """
    And the order "CO123" has a payment id
    And queue should contain message with routing key buyer_portal.buyer_portal_invoice_notification_requested with below data:
    """
    {
      "user": {
        "firstName":"test",
        "lastName":"test",
        "email":"test@ozean12.com",
        "gender":"t"
      },
      "invoiceUuid": "@string@",
      "invoiceAmount": "100000",
      "creditorName": "Behat Merchant",
      "debtorName":"Test User Company",
      "token":"sdg340vpl29kx"
    }
    """
    And queue should contain 1 messages with routing key buyer_portal.buyer_portal_invoice_notification_requested
