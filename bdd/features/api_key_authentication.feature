Feature: As a merchant, i should be able to access all endpoints by providing API key in header

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1

  Scenario: Not providing API key
    When I send a GET request to "/order/XF43Y"
    Then the response status code should be 403
    And the JSON response should be:
	"""
	{"errors":[{"title":"Access Denied.","code":"forbidden"}]}
	"""

  Scenario: Providing wrong API key
    Given I add "X-Api-Key" header equal to WrongKey
    When I send a GET request to "/order/XF43Y"
    Then the response status code should be 401
    And the JSON response should be:
	"""
	{"errors":[{"title":"Unauthorized","code":"unauthorized"}]}
	"""

  Scenario: Providing valid API key
    Given I have a new order "XF43Y2" with amounts 1000/900/100, duration 30 and comment "test order"
    And I get from companies service get debtor response
    And I get from payments service get debtor response
    And I add "X-Api-Key" header equal to test
    When I send a GET request to "/order/XF43Y2"
    Then the response status code should be 200
