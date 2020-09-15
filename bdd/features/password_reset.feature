Feature: Password reset

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1

  Scenario: Successful request of new password
    Given a merchant user exists with uuid "42e8bd74-22ac-4fec-b549-9bc01c353c12" and role admin and permission MANAGE_USERS
    And I get from Oauth service a request password response for user "42e8bd74-22ac-4fec-b549-9bc01c353c12"
    When I send a POST request to "/public/merchant/user/request-new-password" with body:
    """
    {"email": "test@billie.dev"}
    """
    Then the response status code should be 204
    And the response should be empty

  Scenario: Successfully confirmed password reset
    Given I get from Oauth service a confirm password token response
    When I send a GET request to "/public/merchant/user/confirm-password-reset?token=someToken"
    Then the response status code should be 204
    And the response should be empty

  Scenario: Successful password reset
    Given I get from Oauth service a reset password response
    When I send a POST request to "/public/merchant/user/reset-password" with body:
    """
    {
      "password": "someValidPassw0rd",
      "token": "someToken"
    }
    """
    Then the response status code should be 204
    And the response should be empty
