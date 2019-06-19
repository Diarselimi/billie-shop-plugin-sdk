Feature:
    In order to ship an order
    I want to have an end point to ship my orders
    And expect empty response

    Background:
        Given I add "Content-type" header equal to "application/json"
        And I add "X-Test" header equal to 1
        And I add "X-Api-Key" header equal to test

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
        {
            "error": "Order not found"
        }
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
        Then the response status code should be 200
        And the JSON response should be:
        """
        {
          "external_code": "CO123",
          "uuid": "test123",
          "state": "shipped",
          "reasons": null,
          "amount": 1000,
          "amount_net": 900.00,
          "amount_tax": 100.00,
          "debtor_company": {
            "name": "Test User Company",
            "house_number": "10",
            "street": "Heinrich-Heine-Platz",
            "postal_code": "10179",
            "city": "Berlin",
            "country": "DE"
          },
          "bank_account": {
            "iban": "DE1234",
            "bic": "BICISHERE"
          },
          "invoice": {
            "number": "CO123",
            "payout_amount": 1000,
            "outstanding_amount": 1000,
            "fee_amount": 10,
            "fee_rate": 1,
            "due_date": "1978-11-20"
          },
          "debtor_external_data": {
            "name": "test",
            "address_country": "TE",
            "address_postal_code": "test",
            "address_street": "test",
            "address_house": "test",
            "industry_sector": "test",
            "merchant_customer_id":"ext_id"
         },
         "duration":30,
         "dunning_status": null,
         "delivery_address":{
            "house_number":"test",
            "street":"test",
            "city": "test",
            "postal_code":"test",
            "country":"TE"
          }
        }
        """
        And the order "CO123" is in state shipped

    Scenario: Order not shipped if no external code exists nor provided
        Given I have a created order with amounts 1000/900/100, duration 30 and comment "test order"
        And I get from payments service create ticket response
        And I get from companies service get debtor response
        When I send a POST request to "/order/test123/ship" with body:
        """
        {
            "invoice_number": "test",
            "invoice_url": "http://example.com/invoice/is/here",
            "shipping_document_url": "http://example.com/proove/is/here"
        }
        """
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
        Then the response status code should be 400

    Scenario: Order shipped if external code is provided
        Given I have a created order with amounts 1000/900/100, duration 30 and comment "test order"
        And I get from payments service create ticket response
        And I get from companies service identify match response
        And I get from payments service get debtor response
        And I get from payments service get order details response
        And I get from companies service get debtor response
        When I send a POST request to "/order/test123/ship" with body:
        """
        {
            "invoice_number": "test",
            "invoice_url": "http://example.com/invoice/is/here",
            "shipping_document_url": "http://example.com/proove/is/here",
            "external_order_id": "DD123"
        }
        """
        And the order "DD123" is in state shipped
        Then the response status code should be 200
        And the JSON response should be:
        """
        {
          "external_code": "DD123",
          "uuid": "test123",
          "state": "shipped",
          "reasons": null,
          "amount": 1000,
          "amount_net": 900.00,
          "amount_tax": 100.00,
          "debtor_company": {
            "name": "Test User Company",
            "house_number": "10",
            "street": "Heinrich-Heine-Platz",
            "postal_code": "10179",
            "city": "Berlin",
            "country": "DE"
          },
          "bank_account": {
            "iban": "DE1234",
            "bic": "BICISHERE"
          },
          "invoice": {
            "number": "test",
            "payout_amount": 1000,
            "outstanding_amount": 1000,
            "fee_amount": 10,
            "fee_rate": 1,
            "due_date": "1978-11-20"
          },
          "debtor_external_data": {
            "name": "test",
            "address_country": "TE",
            "address_postal_code": "test",
            "address_street": "test",
            "address_house": "test",
            "industry_sector": "test",
            "merchant_customer_id":"ext_id"
          },
            "duration":30,
            "dunning_status": null,
            "delivery_address":{
                "house_number":"test",
                "street":"test",
                "city": "test",
                "postal_code":"test",
                "country":"TE"
          }
        }
        """
