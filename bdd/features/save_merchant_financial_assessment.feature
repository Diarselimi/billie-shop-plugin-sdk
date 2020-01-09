Feature: When I send a request to save I should be able to save financial data for a specific merchant if the data are valid.

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "Authorization" header equal to "Bearer someToken"
    And a merchant user exists with permission MANAGE_ONBOARDING
    And I get from Oauth service a valid user token

  Scenario: Successfully I call the endpoint with the correct data provided
    When I send a POST request to "/merchant/financial-assessment" with body:
    """
    {
      "yearly_transaction_volume": "200",
      "mean_invoice_amount": 123.23,
      "cancellation_rate": 12444.2,
      "invoice_duration": 444,
      "returning_order_rate": 22.0,
      "default_rate": 50.0
    }
    """
    Then the response status code should be 204

  Scenario: I fail to save the data because the number format is not valid.
    When I send a POST request to "/merchant/financial-assessment" with body:
    """
    {
      "yearly_transaction_volume": 200.00,
      "mean_invoice_amount": 123.23,
      "cancellation_rate": 12444.22,
      "invoice_duration": 444,
      "returning_order_rate": 22.00,
      "default_rate": 50.00
    }
    """
    Then the response status code should be 400
    And the JSON response should be:
    """
    {"errors":[{"title":"The number should have have maximum 1 numbers after decimal.","code":"request_validation_error","source":"cancellation_rate"}]}
    """

  Scenario: Fail to save financial data if they already exists for the same merchant which should throw an access denied
    Given I have the following Financial Assessment Data:
    """
    {
      "yearly_transaction_volume": 200.00,
      "mean_invoice_amount": 123.23,
      "cancellation_rate": 12444.2,
      "invoice_duration": 444,
      "returning_order_rate": 22.0,
      "default_rate": 50.0
    }
    """
    And The following onboarding steps are in states for merchant "f2ec4d5e-79f4-40d6-b411-31174b6519ac":
      | name                  | state     |
      | financial_assessment  |  confirmation_pending |
    When I send a POST request to "/merchant/financial-assessment" with body:
    """
    {
      "yearly_transaction_volume": 200.00,
      "mean_invoice_amount": 123.23,
      "cancellation_rate": 12444.2,
      "invoice_duration": 444,
      "returning_order_rate": 22.0,
      "default_rate": 50.0
    }
    """
    Then the JSON response should be:
    """
      {"errors":[{"title":"Merchant Onboarding Step transition is not possible.","code":"forbidden"}]}
    """
    And the response status code should be 403


  Scenario: The request validation fails because of the bad data provided in the request.
    When I send a POST request to "/merchant/financial-assessment" with body:
    """
    {
        "some_bad_datat": "test"
    }
    """
    Then the response status code should be 400
      
  Scenario: The request validation fails because request body is empty.
    When I send a POST request to "/merchant/financial-assessment" with body:
    """
    """
    Then the response status code should be 400
    And the json response should be:
    """
    {"errors":[
      {"title":"This value should not be blank.","code":"request_validation_error","source":"yearly_transaction_volume"},
      {"title":"This value should not be blank.","code":"request_validation_error","source":"mean_invoice_amount"},
      {"title":"This value should not be blank.","code":"request_validation_error","source":"cancellation_rate"},
      {"title":"This value should not be blank.","code":"request_validation_error","source":"invoice_duration"}
    ]}
    """
