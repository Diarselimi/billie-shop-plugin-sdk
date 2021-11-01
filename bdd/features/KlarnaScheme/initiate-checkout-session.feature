Feature: Initialize new checkout session
  In order to know if Billie should be displayed as a payment method
  As klarna scheme
  I want to have an endpoint to get a new session token

  Scenario: Return new checkout session token if it is B2B and in Germany
    When I request "POST /initiate" with body:
      """
      {
        "country": "DE",
        "currency": "EUR",
        "customer": { "type": "organization" },
        "merchant": { "acquirer_merchant_id": "klarna-id" }
      }
      """
    Then the response is 200 with a token in the field "payment_method_session_id"
    And a checkout session was saved with the returned token

  Scenario: Return error if it is B2B but not in Germany
    When I request "POST /initiate" with body:
      """
      {
        "country": "BR",
        "currency": "EUR",
        "customer": { "type": "organization" },
        "merchant": { "acquirer_merchant_id": "klarna-id" }
      }
      """
    Then the response is 200 with body:
      """
      {
        "error_messages": [ "Country 'BR' not supported" ]
      }
      """

  Scenario: Return error if it is B2B in Germany but with unsupported currency
    When I request "POST /initiate" with body:
      """
      {
        "country": "DE",
        "currency": "USD",
        "customer": { "type": "organization" },
        "merchant": { "acquirer_merchant_id": "klarna-id" }
      }
      """
    Then the response is 200 with body:
      """
      {
        "error_messages": [ "Currency 'USD' not supported in country 'DE'" ]
      }
      """

  Scenario: Return error if customer type is not informed
    When I request "POST /initiate" with body:
      """
      {
        "country": "DE",
        "currency": "EUR",
        "merchant": { "acquirer_merchant_id": "klarna-id" }
      }
      """
    Then the response is 200 with body:
      """
      {
        "error_messages": [ "Invalid request" ]
      }
      """

  Scenario: Return error if merchant id is not informed
    When I request "POST /initiate" with body:
      """
      {
        "country": "DE",
        "currency": "EUR",
        "customer": { "type": "organization" }
      }
      """
    Then the response is 200 with body:
      """
      {
        "error_messages": [ "Invalid request" ]
      }
      """

  Scenario: Return error if currency is not informed
    When I request "POST /initiate" with body:
      """
      {
        "country": "DE",
        "customer": { "type": "organization" },
        "merchant": { "acquirer_merchant_id": "klarna-id" }
      }
      """
    Then the response is 200 with body:
      """
      {
        "error_messages": [ "Invalid request" ]
      }
      """
