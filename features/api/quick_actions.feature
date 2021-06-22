@api
Feature:
  In order to see quick actions
  As a user
  I should be able to access API quick actions

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData      |
      | LoadClientData        |
      | LoadCauseData         |
      | LoadGeoZoneData       |
      | LoadQuickActionData   |

  Scenario: As a non logged-in user I can see quick actions of the cause
    Given I send a "GET" request to "/api/causes/55056e7c-2b5f-4ef6-880e-cde0511f79b2/quick_actions"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    [
      {
        "id": "@integer@",
        "title": "Première action rapide de la culture",
        "url": "http://culture.fr"
      },
      {
        "id": "@integer@",
        "title": "Deuxième action rapide de la culture",
        "url": "http://test.culture.fr"
      },
      {
          "id": 3,
          "title": "Troisième action rapide de la culture",
          "url": "http://culture.com"
      }
    ]
    """

  Scenario: As a non logged-in user I see no quick actions of the cause if it has no quick action
    Given I send a "GET" request to "/api/causes/44249b1d-ea10-41e0-b288-5eb74fa886ba/quick_actions"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    []
    """

  Scenario: As a logged-in user I can modify quick actions of the cause
    And I send a "GET" request to "/api/causes/55056e7c-2b5f-4ef6-880e-cde0511f79b2/quick_actions"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    [
      {
        "id": 1,
        "title": "Première action rapide de la culture",
        "url": "http://culture.fr"
      },
      {
        "id": 2,
        "title": "Deuxième action rapide de la culture",
        "url": "http://test.culture.fr"
      },
      {
        "id": 3,
        "title": "Troisième action rapide de la culture",
        "url": "http://culture.com"
      }
    ]
    """
    When I am logged with "carl999@example.fr" via OAuth client "Coalition App"
    And I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/causes/55056e7c-2b5f-4ef6-880e-cde0511f79b2" with body:
    """
    {
        "quick_actions": [
            {
                "id": 1,
                "title": "Nouveau titre",
                "url": "http://new.test"
            },
            {
                "id": 2,
                "title": "Nouveau titre 2"
            },
            {
                "title": "Nouvelle URL",
                "url": "https://new.url.com"
            }
        ]
    }
    """
    Then the response status code should be 200

    When I log out
    And I send a "GET" request to "/api/causes/55056e7c-2b5f-4ef6-880e-cde0511f79b2/quick_actions"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    [
      {
        "id": 1,
        "title": "Nouveau titre",
        "url": "http://new.test"
      },
      {
        "id": 2,
        "title": "Nouveau titre 2",
        "url": "http://test.culture.fr"
      },
      {
        "id": 5,
        "title": "Nouvelle URL",
        "url": "https://new.url.com"
      }
    ]
    """

  Scenario: As a logged-in user I can not modify quick actions of the cause with invalid data
    Given I am logged with "carl999@example.fr" via OAuth client "Coalition App"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/causes/55056e7c-2b5f-4ef6-880e-cde0511f79b2" with body:
    """
    {
        "quick_actions": [
            {
                "id": 1,
                "title": "N",
                "url": ""
            },
            {
                "title": "",
                "url": "azerty"
            }
        ]
    }
    """
    Then the response status code should be 400
    And the JSON should be equal to:
    """
    {
      "type": "https://tools.ietf.org/html/rfc2616#section-10",
      "title": "An error occurred",
      "detail": "quick_actions[0].title: Vous devez saisir au moins 2 caractères.\nquick_actions[0].url: Cette valeur ne doit pas être vide.\nquick_actions[3].title: Cette valeur ne doit pas être vide.\nquick_actions[3].title: Vous devez saisir au moins 2 caractères.\nquick_actions[3].url: Cette valeur n'est pas une URL valide.",
      "violations": [
        {
          "code": "@uuid@",
          "propertyPath": "quick_actions[0].title",
          "message": "Vous devez saisir au moins 2 caractères."
        },
        {
          "code": "@uuid@",
          "propertyPath": "quick_actions[0].url",
          "message": "Cette valeur ne doit pas être vide."
        },
        {
          "code": "@uuid@",
          "propertyPath": "quick_actions[3].title",
          "message": "Cette valeur ne doit pas être vide."
        },
        {
          "code": "@uuid@",
          "propertyPath": "quick_actions[3].title",
          "message": "Vous devez saisir au moins 2 caractères."
        },
        {
          "code": "@uuid@",
          "propertyPath": "quick_actions[3].url",
          "message": "Cette valeur n'est pas une URL valide."
        }
      ]
    }
    """

  Scenario: As a logged-in user I can add quick actions to the cause
    And I send a "GET" request to "/api/causes/017491f9-1953-482e-b491-20418235af1f/quick_actions"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    []
    """
    When I am logged with "carl999@example.fr" via OAuth client "Coalition App"
    And I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/causes/017491f9-1953-482e-b491-20418235af1f" with body:
    """
    {
        "quick_actions": [
            {
                "title": "Action rapide 1",
                "url": "http://action.fr"
            },
            {
                "title": "Action rapide 2",
                "url": "http://action.eu"
            }
        ]
    }
    """
    Then the response status code should be 200

    When I log out
    And I send a "GET" request to "/api/causes/017491f9-1953-482e-b491-20418235af1f/quick_actions"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    [
      {
        "id": "@integer@",
        "title": "Action rapide 1",
        "url": "http://action.fr"
      },
      {
        "id": "@integer@",
        "title": "Action rapide 2",
        "url": "http://action.eu"
      }
    ]
    """

