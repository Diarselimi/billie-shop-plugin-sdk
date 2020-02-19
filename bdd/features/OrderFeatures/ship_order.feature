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

    Scenario: Successful order shipment
        Given I have a created order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
        And I get from payments service create ticket response
        And I get from companies service identify match response
        And I get from payments service get debtor response
        And I get from payments service get order details response
        And I get from companies service get debtor response
        When I send a POST request to "/order/CO123/ship" with body:
        """
        {
            "invoice_number": "CO123",
            "invoice_url": "http://example.com/invoice/is/here",
            "shipping_document_url": "http://example.com/proove/is/here"
        }
        """
        Then the JSON response should be:
        """
        {
           "order_id":"CO123",
           "state":"shipped",
           "reasons":null,
           "decline_reason":null,
           "amount":1000,
           "amount_net":900,
           "amount_tax":100,
           "duration":30,
           "dunning_status":null,
           "debtor_company":{
              "name":"Test User Company",
              "address_house_number":"10",
              "address_street":"Heinrich-Heine-Platz",
              "address_postal_code":"10179",
              "address_city":"Berlin",
              "address_country":"DE"
           },
           "bank_account":{
              "iban":"DE1234",
              "bic":"BICISHERE"
           },
           "invoice":{
              "invoice_number":"CO123",
              "payout_amount":1000,
              "outstanding_amount":1000,
              "fee_amount":10,
              "fee_rate":1,
              "due_date":"1978-11-20",
              "pending_merchant_payment_amount":0,
              "pending_cancellation_amount":0
           },
           "debtor_external_data":{
              "merchant_customer_id":"ext_id",
              "name":"test",
              "address_country":"TE",
              "address_city":"testCity",
              "address_postal_code":"test",
              "address_street":"test",
              "address_house":"test",
              "industry_sector":"test"
           },
           "delivery_address":{
              "house_number":"test",
              "street":"test",
              "city":"test",
              "postal_code":"test",
              "country":"TE"
           },
           "billing_address":{
              "house_number":"test",
              "street":"test",
              "city":"test",
              "postal_code":"test",
              "country":"TE"
           },
           "created_at":"2019-05-20T13:00:00+0200",
           "shipped_at":"2020-02-18T12:04:02+0100"
        }
        """
        And the response status code should be 200
        And the order "CO123" is in state shipped
        And Order notification should exist for order "CO123" with type "order_shipped"

    Scenario: Order not shipped if no external code exists nor provided
        Given I have a created order with amounts 1000/900/100, duration 30 and comment "test order"
        And I get from payments service create ticket response
        And I get from companies service get debtor response
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

    Scenario: Order shipped if external code is provided
        Given I have a created order with amounts 1000/900/100, duration 30 and comment "test order"
        And I get from payments service create ticket response
        And I get from companies service identify match response
        And I get from payments service create ticket response
        And I get from payments service get debtor response
        And I get from payments service get order details response
        And I get from companies service get debtor response
        When I send a POST request to "/order/test-order-uuid/ship" with body:
        """
        {
            "invoice_number": "test",
            "invoice_url": "http://example.com/invoice/is/here",
            "shipping_document_url": "http://example.com/proove/is/here",
            "external_order_id": "DD123"
        }
        """
        Then the response status code should be 200
        And the order "DD123" is in state shipped
        And Order notification should exist for order "DD123" with type "order_shipped"
        And the JSON response should be:
        """
        {
           "order_id":"DD123",
           "state":"shipped",
           "reasons":null,
           "decline_reason":null,
           "amount":1000,
           "amount_net":900,
           "amount_tax":100,
           "duration":30,
           "dunning_status":null,
           "debtor_company":{
              "name":"Test User Company",
              "address_house_number":"10",
              "address_street":"Heinrich-Heine-Platz",
              "address_postal_code":"10179",
              "address_city":"Berlin",
              "address_country":"DE"
           },
           "bank_account":{
              "iban":"DE1234",
              "bic":"BICISHERE"
           },
           "invoice":{
              "invoice_number":"test",
              "payout_amount":1000,
              "outstanding_amount":1000,
              "fee_amount":10,
              "fee_rate":1,
              "due_date":"1978-11-20",
              "pending_merchant_payment_amount":0,
              "pending_cancellation_amount":0
           },
           "debtor_external_data":{
              "merchant_customer_id":"ext_id",
              "name":"test",
              "address_country":"TE",
              "address_city":"testCity",
              "address_postal_code":"test",
              "address_street":"test",
              "address_house":"test",
              "industry_sector":"test"
           },
           "delivery_address":{
              "house_number":"test",
              "street":"test",
              "city":"test",
              "postal_code":"test",
              "country":"TE"
           },
           "billing_address":{
              "house_number":"test",
              "street":"test",
              "city":"test",
              "postal_code":"test",
              "country":"TE"
           },
           "created_at":"2019-05-20T13:00:00+0200",
           "shipped_at":"2020-02-18T12:04:10+0100"
        }
        """
