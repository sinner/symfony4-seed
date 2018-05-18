Feature: Handle user login via the RESTful API

  In order to allow secure access to the system
  As a client software developer
  I need to be able to let users log in and out

  Background:
    Given there are Users with the following details:
      | id | username | email             | password  |
      | 1  | mike     | mike@diocesan.com | mikepass  |
      | 2  | gary     | gary@diocesa.com  | garypass |
    And I set header "Content-Type" with value "application/json"

  Scenario: Cannot GET Login
    When I send a "GET" request to "/login"
    Then the response code should be 405

  Scenario: User can Login with correct credentials
    When I send a "POST" request to "/login" with body:
      """
      {
        "username": "mike",
        "password": "mikepass"
      }
      """
    Then the response code should be 200
    And the response should contain the properties and values:
      | data          | is_success | message |
      | {"token":"*"} | 1          |    *    |

  Scenario: A 401 status code is returned instead of 400 when not credentials are provided.
    When I send a "POST" request to "/login" with body:
      """
      {
      }
      """
    Then the response code should be 401
    And the response should contain the properties and values:
      | is_success | message |
      | 0          |    *    |
    
  Scenario: A 401 status code is returned instead of 400 when only the password is provided.
    When I send a "POST" request to "/login" with body:
      """
      {
        "password": "mikepass"
      }
      """
    Then the response code should be 401
    And the response should contain the properties and values:
      | is_success | message |
      | 0          |    *    |
    
  Scenario: A 401 status code is returned instead of 400 when only the username is provided.
    When I send a "POST" request to "/login" with body:
      """
      {
        "username": "mike"
      }
      """
    Then the response code should be 401
    And the response should contain the properties and values:
      | is_success | message |
      | 0          |    *    |

  Scenario: User cannot Login with bad credentials
    When I send a "POST" request to "/login" with body:
      """
      {
        "username": "fakeuser",
        "password": "badpass"
      }
      """
    Then the response code should be 401
    And the response should contain the properties and values:
       | is_success | message |
       | 0          |    *    |
