Feature: Create a new merchant.

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-User" header equal to 1
    And I start alfred

  Scenario: Successfully create a merchant
    Given I get from alfred "/debtor/560" endpoint response with status 200 and body
        """
        {
            "id": 1,
            "payment_id": "test",
            "name": "Hola Merchant",
            "address_house": "10",
            "address_street": "Heinrich-Heine-Platz",
            "address_city": "Berlin",
            "address_postal_code": "10179",
            "address_country": "DE",
            "address_addition": null,
            "crefo_id": "123",
            "schufa_id": "123",
            "is_blacklisted": 0,
            "payment_id": 1
        }
        """
    When I send a POST request to "/merchant" with body:
      """
      {
          "company_id": "560",
          "merchant_financing_limit": 5000.44,
          "debtor_financing_limit": 700.77,
          "webhook_url": "http://billie.md",
          "webhook_authorization": "X-Api-Key: Hola"
      }
      """
    Then the response status code should be 200
    And the JSON response should have "id"


