Feature: Get current logged in merchant user details

  Background:
	Given I add "Content-type" header equal to "application/json"
	And I add "X-Test" header equal to 1

  Scenario: Missing authorization header
	When I send a GET request to "/merchant/user"
	Then the response status code should be 403
	And the JSON response should be:
	"""
	  {"errors":[{"title":"Access denied","code":"forbidden"}]}
	"""

  Scenario: Successfully retrieve merchant user details
	Given a merchant user exists with role ROLE_VIEW_ORDERS
	And I get from Oauth service a valid user token
	And I get from Oauth service revoke token endpoint a successful response
	And I add "Authorization" header equal to "Bearer SomeTokenHere"
	When I send a GET request to "/public/merchant/user"
	Then the response status code should be 200
	And the JSON response should be:
	"""
	{
		"user_id":1,
		"permissions":[
			"ROLE_VIEW_ORDERS"
		]
	}
	"""
