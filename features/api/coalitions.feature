@api
Feature:
  In order to see coalitions
  As a non logged-in user
  I should be able to access API coalitions

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData      |
      | LoadClientData        |
      | LoadCoalitionData     |

  Scenario: As a non logged-in user I can see first page of active coalitions
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/coalitions"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 19,
            "items_per_page": 2,
            "count": 2,
            "current_page": 1,
            "last_page": 10
        },
        "items": [
            {
                "name": "Culture",
                "description": "Description de la coalition 'Culture'",
                "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315",
                "image_url": "http://test.enmarche.code/assets/images/coalitions/5cd98b474069115622f2f5816014d204.png",
                "followers_count": 4
            },
            {
                "name": "Démocratie",
                "description": "Description de la coalition 'Démocratie'",
                "uuid": "09d700f8-8813-4c3c-9bee-ff18d2051bba",
                "image_url": null,
                "followers_count": 0
            }
        ]
    }
    """

  Scenario: As a non logged-in user I can paginate coalitions with default number of coalitions by page
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/coalitions?page=2"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 19,
            "items_per_page": 2,
            "count": 2,
            "current_page": 2,
            "last_page": 10
        },
        "items": [
            {
                "name": "Economie",
                "description": "Description de la coalition 'Economie'",
                "uuid": "fc7fd104-71e5-4399-a874-f8fe752f846b",
                "image_url": null,
                "followers_count": 0
            },
            {
                "name": "Education",
                "description": "Description de la coalition 'Education'",
                "uuid": "fff11d8d-5cb5-4075-b594-fea265438d65",
                "image_url": null,
                "followers_count": 0
            }
        ]
    }
    """

  Scenario: As a non logged-in user I can paginate coalitions with specific number of coalitions by page
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/coalitions?page=2&page_size=5"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 19,
            "items_per_page": 5,
            "count": 5,
            "current_page": 2,
            "last_page": 4
        },
        "items": [
            {
                "name": "Europe",
                "description": "Description de la coalition 'Europe'",
                "uuid": "0654ae09-ea1a-4142-bea4-2e82dc5da998",
                "image_url": null,
                "followers_count": 0
            },
            {
                "name": "Inclusion",
                "description": "Description de la coalition 'Inclusion'",
                "uuid": "81e4a680-7ce0-4038-b8fe-6bf755db4c5b",
                "image_url": null,
                "followers_count": 0
            },
            {
                "name": "International",
                "description": "Description de la coalition 'International'",
                "uuid": "429fa3a9-8288-4de5-8ba5-366e6afa366b",
                "image_url": null,
                "followers_count": 0
            },
            {
                "name": "Justice",
                "description": "Description de la coalition 'Justice'",
                "uuid": "5b8db218-4da6-4f7f-a53e-29a7a349d45c",
                "image_url": null,
                "followers_count": 0
            },
            {
                "name": "Numérique",
                "description": "Description de la coalition 'Numérique'",
                "uuid": "5e500dbe-5227-4b83-8a9c-8c36f3f25265",
                "image_url": null,
                "followers_count": 0
            }
        ]
    }
    """

  Scenario: As a non logged-in user I can get one coalition by uuid
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/coalitions/d5289058-2a35-4cf0-8f2f-a683d97d8315"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "name": "Culture",
        "description": "Description de la coalition 'Culture'",
        "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315",
        "image_url": "http://test.enmarche.code/assets/images/coalitions/5cd98b474069115622f2f5816014d204.png",
        "followers_count": 4
    }
    """

  Scenario: As a non logged-in user I can not get an inactive coalition
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/coalitions/a82ee43a-c68d-4ed2-9cd5-56eb1f72d9c8"
    Then the response status code should be 404

  Scenario Outline: As a non logged-in user I can not follow/unfollow a coalition
    Given I add "Accept" header equal to "application/json"
    When I send a "PUT" request to "<url>"
    Then the response status code should be 401
    Examples:
      | url                                                               |
      | /api/v3/coalitions/d5289058-2a35-4cf0-8f2f-a683d97d8315/follow    |
      | /api/v3/coalitions/d5289058-2a35-4cf0-8f2f-a683d97d8315/unfollow  |

  Scenario: As a logged-in user I can follow a coalition
    Given I add "Accept" header equal to "application/json"
    And I send a "POST" request to "/oauth/v2/token" with parameters:
      | key           | value                                 |
      | client_id     | 138140b3-1dd2-11b2-ad7e-2348ad4fef66  |
      | client_secret | Ca1#79T6s^kCxqLc9sp$WbtqdOOsdf1iQ     |
      | grant_type    | password                              |
      | username      | carl999@example.fr                    |
      | password      | secret!12345                          |
    And I add the access token to the Authorization header
    When I send a "PUT" request to "/api/v3/coalitions/fff11d8d-5cb5-4075-b594-fea265438d65/follow"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "name": "Education",
        "description": "Description de la coalition 'Education'",
        "uuid": "fff11d8d-5cb5-4075-b594-fea265438d65",
        "image_url": null,
        "followers_count": 1
    }
    """

  Scenario: As a logged-in user I can unfollow a coalition
    Given I add "Accept" header equal to "application/json"
    And I send a "POST" request to "/oauth/v2/token" with parameters:
      | key           | value                                 |
      | client_id     | 138140b3-1dd2-11b2-ad7e-2348ad4fef66  |
      | client_secret | Ca1#79T6s^kCxqLc9sp$WbtqdOOsdf1iQ     |
      | grant_type    | password                              |
      | username      | carl999@example.fr                    |
      | password      | secret!12345                          |
    And I add the access token to the Authorization header
    When I send a "PUT" request to "/api/v3/coalitions/d5289058-2a35-4cf0-8f2f-a683d97d8315/unfollow"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "name": "Culture",
        "description": "Description de la coalition 'Culture'",
        "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315",
        "image_url": "http://test.enmarche.code/assets/images/coalitions/5cd98b474069115622f2f5816014d204.png",
        "followers_count": 3
    }
    """
