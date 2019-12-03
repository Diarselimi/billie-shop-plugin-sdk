Feature:
  In order to transit the merchant onboarding
  I call the merchant onboarding transition endpoint

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1

  Scenario: Merchant onboarding transition succeeded
    When I send a POST request to "/private/merchant/f2ec4d5e-79f4-40d6-b411-31174b6519ac/onboarding/transition" with body:
        """
        {
            "transition": "complete"
        }
        """
    Then the response status code should be 204

  Scenario: Merchant onboarding transition failed, transition not supported
    When I send a POST request to "/private/merchant/f2ec4d5e-79f4-40d6-b411-31174b6519ac/onboarding/transition" with body:
        """
        {
            "transition": "new"
        }
        """
    Then the response status code should be 400
    And the JSON response should be:
        """
        {
            "errors": [
                {
                    "title":"Transition not supported",
                    "code":"request_invalid"
                }
            ]
        }
        """

