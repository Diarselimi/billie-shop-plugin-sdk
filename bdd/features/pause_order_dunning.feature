Feature: As a merchant, i should be able to request pause dunning of an order for a given number of days.

  Background:
	Given I add "Content-type" header equal to "application/json"
	And I add "X-Test" header equal to 1
	And I add "X-Api-Key" header equal to test

  Scenario: Request pause dunning for not existing order
	And I send a POST request to "/order/wrongOrderCode/pause-dunning" with body:
	"""
	  {
		  "number_of_days": 10
	  }
	"""
	Then the response status code should be 404
	And the JSON response should be:
	"""
	  {
		  "error": "Order not found"
	  }
	"""

  Scenario: Invalid number of days provided
	Given I have a late order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
	And I send a POST request to "/order/XF43Y/pause-dunning" with body:
	"""
	  {
		  "number_of_days": 0
	  }
	"""
	Then the response status code should be 400
	And the JSON response should be:
	"""
	  {
		  "errors": [
			  {
				  "source": "number_of_days",
				  "title": "This value should be greater than 0.",
				  "code": "request_validation_error"
			  }
		  ]
	  }
	"""

  Scenario: Failed to pause order dunning because of error from SF
	Given I have a late order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
	And Salesforce API responded for pause dunning request status code 403 and error message "maximum pausing attempts reached"
	And I send a POST request to "/order/XF43Y/pause-dunning" with body:
	"""
	  {
		  "number_of_days": 10
	  }
	"""
	Then the response status code should be 403
	And the JSON response should be:
	"""
	  {
		  "error": "maximum pausing attempts reached"
	  }
	"""

  Scenario: Failed to pause order - order is not in state late
	Given I have a created order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
	And Salesforce API responded for pause dunning request status code 403 and error message "maximum pausing attempts reached"
	And I send a POST request to "/order/XF43Y/pause-dunning" with body:
	"""
	  {
		  "number_of_days": 10
	  }
	"""
	Then the response status code should be 403
	And the JSON response should be:
	"""
	  {
		  "error": "Cannot pause dunning. Order is not in state late"
	  }
	"""

  Scenario: Successfully pause order dunning
	Given I have a late order "XF43Y" with amounts 1000/900/100, duration 30 and comment "test order"
	And Salesforce API responded for pause dunning request with success
	And I send a POST request to "/order/XF43Y/pause-dunning" with body:
	"""
	  {
		  "number_of_days": 10
	  }
	"""
	Then the response status code should be 200
