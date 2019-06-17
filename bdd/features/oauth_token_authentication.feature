Feature: As a merchant, i should be able to access all endpoints
  by providing a valid OAuth token in the Authorization header

  Background:
	Given I add "Content-type" header equal to "application/json"
	And I add "X-Test" header equal to 1

  Scenario: Not providing Token
	When I send a GET request to "/order/XF43Y"
	Then the response status code should be 401
	And the JSON response should be:
	"""
	{"title":"Access denied","code":"access_denied"}
	"""

  Scenario: Providing invalid token
	Given I get from Oauth service invalid token response
	When I add "Authorization" header equal to WrongToken
	And I send a GET request to "/order/XF43Y"
	Then the response status code should be 403
	And the JSON response should be:
	"""
	{"title":"Unauthorized","code":"unauthorized"}
	"""

  Scenario: Providing valid token of a not existing merchant user
	Given I get from Oauth service a valid user token
	When I add "Authorization" header equal to "Bearer someToken"
	And I send a GET request to "/order/XF43Y"
	Then the response status code should be 403
	And the JSON response should be:
	"""
	{"title":"Unauthorized","code":"unauthorized"}
	"""

  Scenario: Providing valid merchant user token
	Given I have a new order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
	And I get from companies service get debtor response
	And I get from payments service get debtor response
	And I get from Oauth service a valid user token
	And a merchant user exists
	When I add "Authorization" header equal to "Bearer someToken"
	And I send a GET request to "/order/XF43Y"
	Then the response status code should be 200

  Scenario: Providing valid merchant client token
	Given I have a new order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
	And I get from companies service get debtor response
	And I get from payments service get debtor response
	And I get from Oauth service a valid client token response
	And a merchant user exists
	When I add "Authorization" header equal to "Bearer someToken"
	And I send a GET request to "/order/XF43Y"
	Then the response status code should be 200

  Scenario: Providing valid merchant client token and X-Api-Key
	Given I have a new order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
	And I get from companies service get debtor response
	And I get from payments service get debtor response
	And I get from Oauth service a valid client token response
	And a merchant user exists
	When I add "Authorization" header equal to "Bearer someToken"
	And I add "X-Api-Key" header equal to WrongKey
	And I send a GET request to "/order/XF43Y"
	Then the response status code should be 200
