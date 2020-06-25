Feature:
  In order to retrieve the external debtor list
  I call the get external debtors endpoint

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "Authorization" header equal to "Bearer someToken"
    And I get from Oauth service a valid user token
    And a merchant user exists with permission CREATE_ORDERS

  Scenario: Successfully search external debtors
    When I get from companies service external debtors response
    And I send a GET request to "/external-debtors?search=test"
    Then the response status code should be 200
    And the JSON response should be:
    """
    [
      {
        "name":"Test User Company",
        "legal_form":"Test legal form",
        "address_street":"Heinrich-Heine-Platz",
        "address_city":"Berlin",
        "address_postal_code":"10179",
        "address_country":"de",
        "address_house_number":"10"
      }
    ]
    """

  Scenario: Fail to search external debtors
    When I send a GET request to "/external-debtors?search="
    Then the response status code should be 400
    And the JSON response should be:
    """
    {
      "errors": [
        {
          "title":"This value should not be blank.",
          "code":"request_validation_error",
          "source":"search_string"
        }
      ]
    }
    """
