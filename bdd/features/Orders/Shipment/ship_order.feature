Feature:
  In order to ship an order
  I want to have an end point to ship my orders And expect empty response

  Background:
    Given I add "Content-type" header equal to "application/json"
    And I add "X-Test" header equal to 1
    And I add "X-Api-Key" header equal to test
    And The following notification settings exist for merchant 1:
      | notification_type | enabled |
      | order_shipped     | 1       |

  Scenario: Order doesn't exist
    When I send a POST request to "/order/ADDDD/ship" with body:
        """
        {
            "invoice_number": "CO123",
            "invoice_url": "http://example.com/invoice/is/here",
            "shipping_document_url": "http://example.com/proove/is/here"
        }
        """
    Then the response status code should be 404
    And the JSON response should be:
        """
        {"errors":[{"title":"Order not found","code":"resource_not_found"}]}
        """


  Scenario: Order not shipped if no external code exists nor provided
    Given I have a created order with amounts 1000/900/100, duration 30 and comment "test order"
    When I send a POST request to "/order/test-order-uuid/ship" with body:
        """
        {
            "invoice_number": "test",
            "invoice_url": "http://example.com/invoice/is/here",
            "shipping_document_url": "http://example.com/proove/is/here"
        }
        """
    Then the response status code should be 400
    And the JSON response should be:
        """
        {
            "errors":[
                {
                    "source":"external_order_id",
                    "title":"This value should not be blank.",
                    "code":"request_validation_error"
                }
            ]
        }
        """
