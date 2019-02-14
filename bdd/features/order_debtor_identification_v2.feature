Feature: In order to test the new identification flow and compare it to the current one, a consumer will consume a
  message with order_id and V1 identified company ID then try to identify the same debtor using V2 logic.

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-User" header equal to 1
    And I start alfred

  Scenario: V1 identified the debtor - v2 identified the same debtor
    Given I have a new order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    And I get from alfred "/debtor/identify/v2" endpoint response with status 200 and body
      """
        {
          "id": 100,
          "payment_id": "test",
          "name": "Test User Company",
          "address_house": "10",
          "address_street": "Heinrich-Heine-Platz",
          "address_city": "Berlin",
          "address_postal_code": "10179",
          "address_country": "DE",
          "address_addition": null,
          "crefo_id": "123",
          "schufa_id": "123",
          "is_blacklisted": 0
        }
      """
    And I push message to paella_events queue and routing key order_debtor_identification_v2_paella with the following content:
      """
      {
        "order_id": 1,
        "v1_company_id": 100
      }
      """
    When I start order_debtor_identification_v2 consumer to consume 1 message
    Then order_identifications table should have a new record with:
      | order_id  | v1_company_id | v2_company_id |
      | 1         | 100           | 100           |

  Scenario: V1 identified the debtor - v2 identified different debtor
    Given I have a new order "CO123" with amounts 1000/900/100, duration 30 and comment "test order"
    And I get from alfred "/debtor/identify/v2" endpoint response with status 200 and body
      """
        {
          "id": 5,
          "payment_id": "test",
          "name": "Test User Company",
          "address_house": "10",
          "address_street": "Heinrich-Heine-Platz",
          "address_city": "Berlin",
          "address_postal_code": "10179",
          "address_country": "DE",
          "address_addition": null,
          "crefo_id": "123",
          "schufa_id": "123",
          "is_blacklisted": 0
        }
      """
    And I push message to paella_events queue and routing key order_debtor_identification_v2_paella with the following content:
      """
      {
        "order_id": 1,
        "v1_company_id": 100
      }
      """
    When I start order_debtor_identification_v2 consumer to consume 1 message
    Then order_identifications table should have a new record with:
      | order_id  | v1_company_id | v2_company_id |
      | 1         | 100           | 5             |
