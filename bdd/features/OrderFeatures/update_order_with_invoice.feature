Feature: Update order with invoice to reduce the order amount

	Background:
		Given I add "Content-type" header equal to "application/json"
		And I add "X-Test" header equal to 1
		And I add "X-Api-Key" header equal to test
		And I get from companies service get debtor response

	Scenario: Order can be updated with invoice (happy path)
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

	Scenario: Order can be updated with invoice same amount
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
