Feature:
    In order to transit the merchant onboarding step
    I call the merchant onboarding step transition endpoint

    Background:
        Given I add "Content-type" header equal to "application/json"
        And I add "X-Test" header equal to 1

    Scenario: Merchant onboarding step transition succeeded
        When I send a POST request to "/private/merchant/f2ec4d5e-79f4-40d6-b411-31174b6519ac/onboarding-step/transition" with body:
        """
        {
            "step": "identity_verification",
            "transition": "complete"
        }
        """
        Then the response status code should be 204

    Scenario: Merchant onboarding step transition failed, transition not supported
        When I send a POST request to "/private/merchant/f2ec4d5e-79f4-40d6-b411-31174b6519ac/onboarding-step/transition" with body:
        """
        {
            "step": "identity_verification",
            "transition": "nonono"
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

    Scenario: Merchant onboarding step transition failed, step not found
        When I send a POST request to "/private/merchant/f2ec4d5e-79f4-40d6-b411-31174b6519ac/onboarding-step/transition" with body:
        """
        {
            "step": "bad_step",
            "transition": "nonono"
        }
        """
        Then the response status code should be 404
        And the JSON response should be:
        """
        {
            "errors": [
                {
                    "title":"Onboarding step not found",
                    "code":"resource_not_found"
                }
            ]
        }
        """
