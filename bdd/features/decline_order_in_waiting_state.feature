Feature: Endpoint to decline an order in waiting state

	Background:
		Given I add "Content-type" header equal to "application/json"
		And I add "X-Test" header equal to 1
		And I add "X-Api-Key" header equal to test

	Scenario: Order doesn't exist
		Given I have a new order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
		When I send a POST request to "/private/order/WrongOrderCode/decline"
		Then the response status code should be 404

	Scenario: Order is not in waiting state
		Given I have a new order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
		When I send a POST request to "/private/order/test-order-uuid/decline"
		Then the response status code should be 403
		And the JSON response should be:
		"""
      {"errors":[{"title":"Cannot decline the order. Order is not in waiting/pre_approved state.","code":"forbidden"}]}
		"""

	Scenario: Successfully decline order in waiting state
		Given I have a waiting order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
		And I get from companies service get debtor response
		And The following notification settings exist for merchant 1:
			| notification_type | enabled |
			| order_declined    | 1       |
		When I send a POST request to "/private/order/test-order-uuid/decline"
		Then the response status code should be 204
		And the order CO123 is in state declined
		And Order notification should exist for order "CO123" with type "order_declined"
