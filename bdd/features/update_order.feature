Feature:
  In order to update an order
  I want to have an end point to update my orders
  And expect empty response

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-Key" header equal to test
    And I get from payments service modify ticket response
    And I get from companies service get debtor response
    And I get from companies service "/debtor/1/unlock" endpoint response with status 200 and body
    """
    """

  Scenario: Case 1: Order exists, not yet shipped, due date provided, amount unchanged
    Given I have a new order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a PATCH request to "/order/CO123" with body:
    """
        {
          "duration": 50,
          "amount": {
            "gross": 1000,
            "net": 900,
            "tax": 100
          }
        }
    """
    Then the response status code should be 412
    And the JSON response should be:
    """
    {"errors":[{"title":"Update duration not possible","code":"order_duration_update_not_possible"}]}
    """
    And merchant debtor has financing power 1000

  Scenario: Case 1.1: Order exists, not yet shipped, due date provided, valid new amount
    Given I have a new order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a PATCH request to "/order/CO123" with body:
    """
    {
      "amount": {
        "gross": 500,
        "net": 400,
        "tax": 100
      }
    }
    """
    Then the response status code should be 204
    And the response should be empty
    And the order "CO123" duration is 30
    And the order "CO123" amountGross is 500
    And merchant debtor has financing power 1500

  Scenario: Case 2: Order exists, not yet shipped, due date unchanged/not set, valid new amount
    Given I have a new order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a PATCH request to "/order/CO123" with body:
    """
    {
      "amount": {
        "gross": 500,
        "net": 400,
        "tax": 100
      }
    }
    """
    Then the response status code should be 204
    And the response should be empty
    And the order "CO123" duration is 30
    And the order "CO123" amountGross is 500
    And the order "CO123" amountNet is 400
    And the order "CO123" amountTax is 100
    And merchant debtor has financing power 1500

  Scenario: Case 3: Order exists, is shipped but not paid back, valid new due date*, amount unchanged
    Given I have a shipped order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    And I get from payments service create ticket response
    When I send a PATCH request to "/order/CO123" with body:
        """
        {
          "duration": 50,
          "amount": {
            "gross": 1000,
            "net": 900,
            "tax": 100
          }
        }
        """
    Then the response status code should be 204
    And the response should be empty
    And the order "CO123" duration is 50
    And the order "CO123" amountGross is 1000
    And merchant debtor has financing power 1000

  Scenario: Case 3.1: Order exists, is shipped but not paid back, valid new due date*, amount unchanged
    Given I have a shipped order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a PATCH request to "/order/CO123" with body:
        """
        {
          "duration": 121,
          "amount": {
            "gross": 500,
            "net": 400,
            "tax": 100
          }
        }
        """
    Then the response status code should be 400
    And the JSON response should be:
    """
      {
       "errors":[
          {
            "source":"duration",
            "title":"This value should be 120 or less.",
            "code":"request_validation_error"
          }
       ]
      }
    """
    And merchant debtor has financing power 1000

  Scenario: Case 4: Order exists, is shipped but not paid back, due date unchanged, new valid amount
    Given I have a shipped order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a PATCH request to "/order/CO123" with body:
        """
        {
          "duration": 30,
          "amount": {
            "gross": 500,
            "net": 400,
            "tax": 100
          },
          "invoice_number": "DE12",
          "invoice_url": "http://google.de"
        }
        """
    Then the response status code should be 204
    And the response should be empty
    And the order "CO123" duration is 30
    And the order "CO123" amountGross is 500
    And the order "CO123" amountNet is 400
    And the order "CO123" amountTax is 100
    And the order "CO123" invoiceNumber is DE12
    And the order "CO123" invoiceUrl is "http://google.de"
    And merchant debtor has financing power 1000

  Scenario: Case 5: Order exists, is shipped but not paid back, new duration invalid
    Given I have a shipped order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a PATCH request to "/order/CO123" with body:
    """
    {
        "duration": "THIS IS NOT A DURATION"
    }
    """
    Then the response status code should be 400
    And the JSON response should be:
    """
    {"errors":[{"source":"duration","title":"This value should be of type int.","code":"request_validation_error"},{"source":"duration","title":"This value should be a valid number.","code":"request_validation_error"}]}
    """
    And merchant debtor has financing power 1000

  Scenario: Case 6: Order exists, is shipped but not paid back, new amount invalid
    Given I have a shipped order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a PATCH request to "/order/CO123" with body:
        """
        {
          "amount": {
            "gross": 2000,
            "net": 1800,
            "tax": 200
          }
        }
        """
    Then the response status code should be 412
    And the JSON response should be:
    """
    {"errors":[{"title":"Invalid amount","code":"order_validation_failed"}]}
    """
    And merchant debtor has financing power 1000

  Scenario: Case 7: Order exists, is shipped but not paid back, valid new due date, valid new amount
    Given I have a shipped order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    And I get from payments service two modify ticket responses
    When I send a PATCH request to "/order/CO123" with body:
        """
        {
            "invoice_number": "UPDATED_CO123",
            "invoice_url": "/invoice.pdf",
            "duration": 50,
            "amount": {
                "net": 500,
                "gross": 500,
                "tax": 0
            }
        }
        """
    Then the response status code should be 204
    And the order "CO123" duration is 50
    And the order "CO123" amountGross is 500
    And the order "CO123" amountNet is 500
    And the order "CO123" amountTax is 0
    And the order "CO123" invoiceNumber is UPDATED_CO123
    And the order "CO123" invoiceUrl is "/invoice.pdf"
    And merchant debtor has financing power 1000

  Scenario: Case 8: Order does not exist
    When I send a PATCH request to "/order/CO123XX" with body:
    """
    {
        "duration": 50,
        "amount": {
            "net": 500,
            "gross": 500,
            "tax": 0
        }
    }
    """
    Then the response status code should be 404

  Scenario: Case 9: Order was marked as fraud
    Given I have a created order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    And The order "CO123" was already marked as fraud
    When I send a PATCH request to "/order/CO123" with body:
    """
        {
          "duration": 30,
          "amount": {
            "gross": 500,
            "net": 400,
            "tax": 100
          }
        }
    """
    Then the response status code should be 403
    And the JSON response should be:
        """
        {"errors":[{"title":"Order was marked as fraud","code":"forbidden"}]}
        """

  Scenario: Case 10: Order exists, is shipped, valid new invoice number and url
    Given I have a shipped order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a PATCH request to "/order/CO123" with body:
        """
        {
          "invoice_number": "newInvoiceNumber",
          "invoice_url": "/newInvoice.pdf"
        }
        """
    Then the response status code should be 204
    And the response should be empty
    And the order "CO123" invoiceNumber is "newInvoiceNumber"
    And the order "CO123" invoiceUrl is "/newInvoice.pdf"
    And merchant debtor has financing power 1000

  Scenario: Case 11: Order exists, not yet shipped, valid new invoice number and url
    Given I have a new order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a PATCH request to "/order/CO123" with body:
        """
        {
          "invoice_number": "newInvoiceNumber",
          "invoice_url": "/newInvoice.pdf"
        }
        """
    Then the response status code should be 412
    And the JSON response should be:
    """
    {"errors":[{"title":"Update invoice is not possible","code":"order_invoice_update_not_possible"}]}
    """
    And merchant debtor has financing power 1000

  Scenario: Case 12: Order exists, not yet shipped, invalid amounts provided
    Given I have a new order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a PATCH request to "/order/CO123" with body:
        """
        {
          "amount": {
            "gross": 500,
            "net": 40,
            "tax": 10
          }
        }
        """
    Then the response status code should be 400
    And the JSON response should be:
      """
      {
        "errors":[
          {
             "source":"amount",
             "title":"Invalid amounts",
             "code":"request_validation_error"
          }
        ]
      }
      """
    And merchant debtor has financing power 1000
