Feature: Register merchant user to access dashboard

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1

  Scenario: successfully register merchant user
    Given I successfully create OAuth client with email "test@merchantX.com" and user id "test-auth-id"
    When I send a POST request to "/private/merchant/1/user" with body:
	"""
	{
		"first_name": "name",
		"last_name": "last",
		"email": "test@merchantX.com",
		"password": "testPassword"
	}
	"""
    Then the response status code should be 201
    And merchant user with merchant id 1 and user id "test-auth-id" should be created

  Scenario: validation error
    When I send a POST request to "/private/merchant/1/user" with body:
	"""
	{
		"first_name": "name",
		"last_name": "last",
		"email": "test@.com",
		"password": ""
	}
	"""
    Then the response status code should be 400
    And the JSON response should be:
    """
    {
	   "errors":[
		  {
			 "source":"user_email",
			 "title":"This value is not a valid email address.",
			 "code":"request_validation_error"
		  },
		  {
			 "source":"user_password",
			 "title":"This value should not be blank.",
			 "code":"request_validation_error"
		  }
	   ]
	}
    """

  Scenario: validation error first and last name not provided
    When I send a POST request to "/private/merchant/1/user" with body:
	"""
	{
		"email": "test@merchantX.com",
		"password": "testPassword"
	}
	"""
    Then the response status code should be 400
    And the JSON response should be:
    """
    {
	   "errors":[
		  {
			 "source":"first_name",
			 "title":"This value should not be blank.",
			 "code":"request_validation_error"
		  },
		  {
			 "source":"last_name",
			 "title":"This value should not be blank.",
			 "code":"request_validation_error"
		  }
	   ]
	}
    """

