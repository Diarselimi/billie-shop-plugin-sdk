Feature: Check invoice owner, to prevent exposing sensitive data

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "Authorization" header equal to "Bearer someToken"
    And I get from Oauth service a valid user token
    And a merchant user exists with permission VIEW_ORDERS

  Scenario: Check invoice owner
    Given I have a new order "ABCDE" with amounts 1000/900/100, duration 30 and checkout session "208cfe7d-046f-4162-b175-748942d6cff2"
    And the following invoice data exists:
      | order_id | invoice_uuid                         |
      | 1        | 208cfe7d-046f-4162-b175-748942d6cff4 |
    When I send a GET request to "/invoices/208cfe7d-046f-4162-b175-748942d6cff4/check-owner"
    Then the response status code should be 204
    And the response should be empty
