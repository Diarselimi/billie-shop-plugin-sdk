Feature:
  As a debtor I want to provide my IBAN to pay via Direct Debit for the checkout order from the widget.

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-Key" header equal to test
    And The following risk check definitions exist:
      | name                      |
      | iban_fraud                |

  Scenario: I successfully provide the IBAN for checkout session order.
    Given I have a authorized order "CO123" with amounts 100.0/90.0/10.0, duration 30 and checkout session "123123CO123" and uuid "cb92625e-e4cb-4cf7-89ee-5d438e313828"
    And the checkout_session_id "123123CO123" should be invalid
    And GraphQL will respond to getMerchantDebtorDetails query
    And I get from Fraud service a non fraud iban response
    And I get from Sepa service generate mandate good response
    And I get from Banco service get bank good response
    And I send a POST request to "/checkout-session/123123CO123/iban" with body:
    """
      {
        "iban": "DE42500105172497563393",
        "bank_account_owner": "Edeka Co Kg"
      }
    """
    Then the response status code should be 200
    And the JSON response should be:
    """
    {
      "iban": "DE42500105172497563393",
      "mandate_reference": "YGG6VI5RQ4OR3GJ0",
      "creditor_name": "Billie GmbH",
      "creditor_identifier": "DE26ZZZ00001981599"
    }
    """
    And the order CO123 is in state authorized
    And The following risk check results exist for order CO123:
      | check_name                | is_passed |
      | iban_fraud                | 1         |

  Scenario: I get error if I provide a fraud IBAN for checkout session order.
    Given I have a authorized order "CO123" with amounts 100.0/90.0/10.0, duration 30 and checkout session "123123CO123"
    And the checkout_session_id "123123CO123" should be invalid
    And I get from Fraud service a fraud iban response
    And I send a POST request to "/checkout-session/123123CO123/iban" with body:
    """
      {
        "iban": "DE42500105172497563393",
        "bank_account_owner": "Edeka Co Kg"
      }
    """
    Then the response status code should be 403
    Then the JSON response should be:
    """
    {
      "errors":[{
        "title":"IBAN is not allowed",
        "code":"forbidden"
      }]
    }
    """
    And the order CO123 is in state declined
    And The following risk check results exist for order CO123:
      | check_name                | is_passed |
      | iban_fraud                | 0         |


