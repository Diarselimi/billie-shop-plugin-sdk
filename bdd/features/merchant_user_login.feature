Feature: Enable merchant users to login

  Background:
	Given I add "Content-type" header equal to "application/json"
	And I add "X-Test" header equal to 1

  Scenario: Invalid request - empty and invalid values
	When I send a POST request to "/public/merchant/user/login" with body:
	"""
	  {
		"email": "d",
		"password": ""
	  }
	"""
	Then the response status code should be 400
	And the JSON response should be:
	"""
	  {
		"errors": [
		  {
			"source": "email",
			"title": "This value is not a valid email address.",
			"code": "request_validation_error"
		  },
		  {
			"source": "password",
			"title": "This value should not be blank.",
			"code": "request_validation_error"
		  }
		]
	  }
	"""

  Scenario: Invalid request - missing fields
	When I send a POST request to "/public/merchant/user/login" with body:
	"""
	  {}
	"""
	Then the response status code should be 400
	And the JSON response should be:
	"""
	  {
		"errors": [
		  {
			"source": "email",
			"title": "This value should not be blank.",
			"code": "request_validation_error"
		  },
		  {
			"source": "password",
			"title": "This value should not be blank.",
			"code": "request_validation_error"
		  }
		]
	  }
	"""

  Scenario: Invalid credentials
	Given I get from Oauth service invalid credentials response
	When I send a POST request to "/merchant/user/login" with body:
	"""
	  {
		"email": "testUser@merchant.com",
		"password": "testPassword"
	  }
	"""
	Then the response status code should be 401

  Scenario: Valid credentials - successful login
	Given a merchant user exists with overridden permission VIEW_DEBTORS
	And I successfully obtain token from oauth service
	And I get from Oauth service a valid user token
	When I send a POST request to "/merchant/user/login" with body:
	"""
	  {
		"email": "testUser@merchant.com",
		"password": "testPassword"
	  }
	"""
	Then the response status code should be 200
	And the JSON response should be:
	"""
	  {
	  	"user_id": 1,
    	"access_token": "testToken",
    	"permissions": [
        	"VIEW_DEBTORS"
    	],
    	"merchant_name": "Behat User"
	  }
	"""
