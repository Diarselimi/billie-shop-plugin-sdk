Feature: Update order with invoice to reduce the order amount

	Background:
		Given I add "Content-type" header equal to "application/json"
		And I add "X-Test" header equal to 1
		And I get from companies service get debtor response

	Scenario: Order amount can be updated before shipment
		Given I have a "created" order with amounts 1000/900/100, duration 30 and comment "test order"
		And a merchant user exists with permission UPDATE_ORDERS
		And I get from Oauth service a valid user token
		And I add "Authorization" header equal to "Bearer someToken"
		When I send a POST request to "/order-with-invoice/test-order-uuid" with body:
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
		And the order with uuid "test-order-uuid" should have amounts 500/400/100

	Scenario: Order can be updated before shipment (and same amount)
		Given I have a "created" order with amounts 1000/900/100, duration 30 and comment "test order"
		And a merchant user exists with permission UPDATE_ORDERS
		And I get from Oauth service a valid user token
		And I add "Authorization" header equal to "Bearer someToken"
		When I send a POST request to "/order-with-invoice/test-order-uuid" with body:
    """
    {
      "amount": {
        "gross": 1000,
        "net": 900,
        "tax": 100
      }
    }
    """
		Then the response status code should be 204
		And the response should be empty
		And the order with uuid "test-order-uuid" should have amounts 1000/900/100

	Scenario: Order invoice file and invoice number can be updated after shipment
		Given I have a "shipped" order with amounts 1000/900/100, duration 30 and comment "test order"
		And a merchant user exists with permission UPDATE_ORDERS
		And I get from Oauth service a valid user token
		And I add "Authorization" header equal to "Bearer someToken"
		And I get from files service a good response
		When I send a POST request to "/order-with-invoice/test-order-uuid" with parameters:
			| key               | value                             |
			| invoice_number    | 555                               |
			| invoice_file      | @dummy-invoice.png                |
			| amount												| {"gross":500,"net":400,"tax":100} |
		Then the order "test-order-uuid" has invoice data
  And the order with uuid "test-order-uuid" should have invoice number "555"
