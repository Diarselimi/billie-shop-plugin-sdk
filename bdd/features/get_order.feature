Feature:
  In order to retrieve the order details
  I want to call the get order endpoint

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-Key" header equal to test

  Scenario: Unsuccessful order retrieve - order doesn't exist
    When I send a GET request to "/order/ABC"
    Then the response status code should be 404
    And the JSON response should be:
    """
    {"errors":[{"title":"Order not found","code":"resource_not_found"}]}
    """

    Scenario: Successful order retrieval
      Given I have a new order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
      And I get from companies service identify match and good decision response
      And I get from companies service update debtor positive response
      And I get from payments service get debtor response
      When I send a GET request to "/order/XF43Y"
      Then the response status code should be 200
      And the JSON response should be:
      """
      {
          "order_id": "XF43Y",
          "state": "new",
          "reasons": null,
          "amount": 1000,
          "amount_net": 900,
          "amount_tax": 100,
          "created_at": "2019-05-20T13:00:00+0200",
          "debtor_company": {
              "name": "Test User Company",
              "address_house_number": "10",
              "address_street": "Heinrich-Heine-Platz",
              "address_postal_code": "10179",
              "address_city": "Berlin",
              "address_country": "DE"
          },
          "bank_account": {
              "iban": "DE1234",
              "bic": "BICISHERE"
          },
          "invoice": {
              "invoice_number": null,
              "payout_amount": null,
              "outstanding_amount":null,
              "fee_amount": null,
              "fee_rate": null,
              "due_date": null
          },
          "debtor_external_data": {
              "name": "test",
              "address_country": "TE",
              "address_city": "testCity",
              "address_postal_code": "test",
              "address_street": "test",
              "address_house": "test",
              "industry_sector": "test",
              "merchant_customer_id":"ext_id"
         },
         "duration":30,
         "dunning_status": null,
         "shipped_at":null,
         "delivery_address":{
            "house_number":"test",
            "street":"test",
            "city": "test",
            "postal_code":"test",
            "country":"TE"
         }
      }
      """

  Scenario: Successful declined order retrieval
    Given I have a declined order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
    And I get from companies service get debtor response
    And I get from companies service get debtor response
    When I send a GET request to "/order/XF43Y"
    Then the response status code should be 200
    And the JSON response should be:
    """
    {
        "order_id": "XF43Y",
        "uuid": "test123",
        "state": "declined",
        "reasons": "risk_policy",
        "amount": 1000,
        "amount_net": 900,
        "amount_tax": 100,
        "created_at": "2019-05-20T13:00:00+0200",
        "debtor_company": {
            "name": "Test User Company",
            "address_house_number": "10",
            "address_street": "Heinrich-Heine-Platz",
            "address_postal_code": "10179",
            "address_city": "Berlin",
            "address_country": "DE"
        },
         "bank_account": {
            "iban": null,
            "bic": null
        },
        "invoice": {
            "invoice_number": null,
            "payout_amount": null,
            "outstanding_amount":null,
            "fee_amount": null,
            "fee_rate": null,
            "due_date": null
        },
        "debtor_external_data": {
            "name": "test",
            "address_country": "TE",
            "address_city": "testCity",
            "address_postal_code": "test",
            "address_street": "test",
            "address_house": "test",
            "industry_sector": "test",
            "merchant_customer_id":"ext_id"
         },
         "duration": 30,
         "dunning_status": null,
         "shipped_at": null,
         "delivery_address":{
            "house_number":"test",
            "street":"test",
            "city": "test",
            "postal_code":"test",
            "country":"TE"
         }
    }
    """

  Scenario: Successful late order retrieval
    Given I have a late order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
    And I get from companies service identify match response
    And I get from payments service get debtor response
    And I get from payments service get order details response
    And I get from companies service get debtor response
    And I get from salesforce dunning status endpoint "Created" status
    When I send a GET request to "/order/XF43Y"
    Then the response status code should be 200
    And the JSON response should be:
    """
    {
        "order_id": "XF43Y",
        "uuid": "test123",
        "state": "late",
        "reasons": null,
        "amount": 1000,
        "amount_net": 900,
        "amount_tax": 100,
        "duration": 30,
        "dunning_status": "active",
        "debtor_company": {
            "name": "Test User Company",
            "address_house_number": "10",
            "address_street": "Heinrich-Heine-Platz",
            "address_postal_code": "10179",
            "address_city": "Berlin",
            "address_country": "DE"
        },
        "bank_account": {
            "iban": "DE1234",
            "bic": "BICISHERE"
        },
        "invoice": {
            "invoice_number": null,
            "payout_amount": 1000,
            "outstanding_amount": 1000,
            "fee_amount": 10,
            "fee_rate": 1,
            "due_date": "1978-11-20"
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
        "created_at": "2019-05-20T13:00:00+0200",
        "shipped_at": null
    }
    """

  Scenario: Successful complete order retrieval
    Given I have a complete order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
    And I get from companies service identify match response
    And I get from payments service get debtor response
    And I get from payments service get order details response
    And I get from companies service get debtor response
    When I send a GET request to "/order/XF43Y"
    Then the response status code should be 200
    And the JSON response should be:
    """
    {
        "order_id": "XF43Y",
        "uuid": "test123",
        "state": "complete",
        "reasons": null,
        "amount": 1000,
        "amount_net": 900,
        "amount_tax": 100,
        "duration": 30,
        "dunning_status": null,
        "debtor_company": {
            "address_city": "Berlin",
            "address_country": "DE",
            "address_house_number": "10",
            "address_postal_code": "10179",
            "address_street": "Heinrich-Heine-Platz",
            "name": "Test User Company"
        },
        "bank_account": {
            "iban": "DE1234",
            "bic": "BICISHERE"
        },
        "invoice": {
            "invoice_number": null,
            "payout_amount": 1000,
            "outstanding_amount": 1000,
            "fee_amount": 10,
            "fee_rate": 1,
            "due_date": "1978-11-20"
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
        "created_at": "2019-05-20T13:00:00+0200",
        "shipped_at": null
    }
    """
