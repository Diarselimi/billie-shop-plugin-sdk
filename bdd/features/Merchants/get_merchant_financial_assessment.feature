Feature: Get financial assessments feature.

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "Authorization" header equal to "Bearer someToken"
    And a merchant user exists with permission MANAGE_ONBOARDING
    And I get from Oauth service a valid user token

  Scenario: Successfully I call the endpoint with the correct data provided
    Given I have the following Financial Assessment Data:
    """
    {
      "yearly_transaction_volume":200,
      "mean_invoice_amount":123.23,
      "cancellation_rate":12444.2,
      "invoice_duration":444,
      "returning_order_rate":22.0,
      "default_rate":50.0,
      "high_invoice_amount":20000.43,
      "digital_goods_rate":50.0
    }
    """
    When I send a GET request to "/merchant/financial-assessment"
    Then the response status code should be 200
    And the json response should be:
    """
    {
      "yearly_transaction_volume": 200,
      "mean_invoice_amount": 123.23,
      "cancellation_rate": 12444.2,
      "invoice_duration": 444,
      "returning_order_rate": 22.0,
      "default_rate": 50.0,
      "high_invoice_amount": 20000.43,
      "digital_goods_rate": 50.0
    }
    """

  Scenario: There are no data for the current merchant
    When I send a GET request to "/merchant/financial-assessment"
    Then the response status code should be 404

