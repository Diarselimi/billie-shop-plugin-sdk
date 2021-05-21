Feature: As a merchant, i should be able to access all endpoints by providing API key in header

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1

  Scenario: Not providing API key
    When I send a GET request to "/order/XF43Y"
    Then the response status code should be 401
    And the JSON response should be:
    """
    {"errors":[{"title":"Unauthorized","code":"unauthorized"}]}
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
    And I get from invoice-butler service good response
    And the following invoice data exists:
      | order_id | invoice_uuid                         |
      | 1        | 208cfe7d-046f-4162-b175-748942d6cff4 |
    And GraphQL will respond to getMerchantDebtorDetails query
    And I add "X-Api-Key" header equal to test
    And I get from payments service get order details response
    When I send a GET request to "/order/XF43Y2"
    Then the response status code should be 200
