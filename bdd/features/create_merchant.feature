Feature: Create a new merchant.

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-User" header equal to 1

  Scenario: Successfully create a merchant
    Given I get from companies service get debtor response
    When I send a POST request to "/merchant" with body:
      """
      {
          "company_id": "1",
          "merchant_financing_limit": 5000.44,
          "debtor_financing_limit": 700.77,
          "webhook_url": "http://billie.md",
          "webhook_authorization": "X-Api-Key: Hola"
      }
      """
    Then the response status code should be 200
    And the JSON response should have "id"


