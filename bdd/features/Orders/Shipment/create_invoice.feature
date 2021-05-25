Feature:
  In order to ship or partially ship an order we need to create invoices for those orders.

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-Key" header equal to test
    And GraphQL will respond to getMerchantDebtorDetails query
    And I get from files service a good response
    And a merchant user exists with permission SHIP_ORDERS
    And I get from Oauth service a valid user token
    And I add "Authorization" header equal to "Bearer someToken"

  Scenario: I successfully partially ship an order
    Given I have a created v2 order "CO124" with amounts 1000/900/100, duration 30 and comment "test order"
    And I get from invoice-butler service no invoices response
    When I send a POST request to "/invoices" with body:
    """
    {
      "orders": [
        "CO124"
      ],
      "external_code": "some-string",
      "invoice_url": "string",
      "shipping_document_url": "string",
      "amount": {
        "gross": 260.27,
        "net": 255.12,
        "tax": 5.15
      }
    }
    """
    Then the response status code should be 201
    And the order CO124 is in state partially_shipped


  Scenario: I successfully fully ship an order
    Given I have a created v2 order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    And I get from invoice-butler service no invoices response
    When I send a POST request to "/invoices" with body:
    """
    {
      "orders": [
        "CO123"
      ],
      "external_code": "string",
      "invoice_url": "string",
      "shipping_document_url": "string",
      "amount": {
        "gross": 1000,
        "net": 900,
        "tax": 100
      }
    }
    """
    Then the response status code should be 201
    And the order CO123 is in state shipped
