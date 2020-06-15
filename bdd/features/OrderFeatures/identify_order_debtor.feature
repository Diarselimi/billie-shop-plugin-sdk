Feature:
  I want to rerun the debtor identification for a specific order

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1

  Scenario: Order doesn't exist
    When I send a GET request to "/private/order/ABC/identify-debtor"
    Then the response status code should be 404
    And the JSON response should be:
    """
    {"errors":[{"title":"Order not found","code":"resource_not_found"}]}
    """

  Scenario: Order debtor not identified
    Given I have a new order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
    And I get from companies service identify no match response
    When I send a GET request to "/private/order/test-order-uuidXF43Y/identify-debtor"
				Then the response status code should be 404
				And the JSON response should be:
    """
    {"errors":[{"title":"Debtor not identified","code":"resource_not_found"}]}
    """

		Scenario: Order debtor identified
			Given I have a new order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
			And I get from companies service identify match response
			When I send a GET request to "/private/order/test-order-uuidXF43Y/identify-debtor"
			Then the json response should be:
			"""
			{
				"identified_debtor": {"name":"Test User Company","address_house_number":"10","address_street":"Heinrich-Heine-Platz","address_postal_code":"10179","address_city":"Berlin","address_country":"DE"}
			}
			"""
			And the response should contain "uuid"
