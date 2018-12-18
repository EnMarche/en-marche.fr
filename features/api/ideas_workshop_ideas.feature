@api
Feature:
  In order to see ideas
  As a non logged-in user
  I should be able to access API Ideas Workshop

  Background:
    Given the following fixtures are loaded:
      | LoadIdeaData              |
      | LoadIdeaThreadCommentData |
      | LoadIdeaVoteData          |

  Scenario: As a non logged-in user I can see published ideas
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/ideas?status=FINALIZED"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    [
        {
            "theme": {
                "name": "Armées et défense"
            },
            "category": {
                "name": "Echelle Européenne",
                "enabled": true
            },
            "needs": [],
            "author": {
                "first_name": "Jacques",
                "last_name": "Picard"
            },
            "published_at": "2018-12-04T10:00:00+01:00",
            "committee": null,
            "status": "FINALIZED",
            "with_committee": false,
            "votes_count": 0,
            "author_category": "ADHERENT",
            "description": "In nec risus vitae lectus luctus fringilla. Suspendisse vitae enim interdum, maximus justo a, elementum lectus. Mauris et augue et magna imperdiet eleifend a nec tortor.",
            "created_at": "@string@.isDateTime()",
            "name": "Réduire le gaspillage",
            "slug": "reduire-le-gaspillage",
            "days_before_deadline": "@integer@",
            "contributors_count": 0,
            "comments_count": 0
        }
    ]
    """

  Scenario: As a non logged-in user I can see pending ideas
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/ideas?status=PENDING"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    [
        {
            "theme": {
                "name": "Armées et défense"
            },
            "category": {
                "name": "Echelle Européenne",
                "enabled": true
            },
            "needs": [
                {
                    "name": "Juridique",
                    "enabled": true
                }
            ],
            "author": {
                "first_name": "Jacques",
                "last_name": "Picard"
            },
            "published_at": "2018-12-01T10:00:00+01:00",
            "committee": {
                "created_at": "2017-01-12T13:25:54+01:00",
                "name": "En Marche Paris 8",
                "slug": "en-marche-paris-8"
            },
            "status": "PENDING",
            "with_committee": true,
            "votes_count": 21,
            "author_category": "COMMITTEE",
            "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec maximus convallis dolor, id ultricies lorem lobortis et. Vivamus bibendum leo et ullamcorper dapibus.",
            "created_at": "@string@.isDateTime()",
            "name": "Faire la paix",
            "slug": "faire-la-paix",
            "days_before_deadline": "@integer@",
            "contributors_count": 0,
            "comments_count": 4
        }
    ]
    """

  Scenario: As a non logged-in user I can filter ideas by name
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/ideas?name=paix"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    [
        {
            "theme": {
                "name": "Armées et défense"
            },
            "category": {
                "name": "Echelle Européenne",
                "enabled": true
            },
            "needs": [
                {
                    "name": "Juridique",
                    "enabled": true
                }
            ],
            "author": {
                "first_name": "Jacques",
                "last_name": "Picard"
            },
            "published_at": "2018-12-01T10:00:00+01:00",
            "committee": {
                "created_at": "2017-01-12T13:25:54+01:00",
                "name": "En Marche Paris 8",
                "slug": "en-marche-paris-8"
            },
            "status": "PENDING",
            "with_committee": true,
            "votes_count": 21,
            "author_category": "COMMITTEE",
            "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec maximus convallis dolor, id ultricies lorem lobortis et. Vivamus bibendum leo et ullamcorper dapibus.",
            "created_at": "@string@.isDateTime()",
            "name": "Faire la paix",
            "slug": "faire-la-paix",
            "days_before_deadline": "@integer@",
            "contributors_count": 0,
            "comments_count": 4
        }
    ]
    """

  Scenario: As a non logged-in user I can filter ideas by theme
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/ideas?theme.name=defense"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    [
        {
            "theme": {
                "name": "Armées et défense"
            },
            "category": {
                "name": "Echelle Européenne",
                "enabled": true
            },
            "needs": [
                {
                    "name": "Juridique",
                    "enabled": true
                }
            ],
            "author": {
                "first_name": "Jacques",
                "last_name": "Picard"
            },
            "published_at": "2018-12-01T10:00:00+01:00",
            "committee": {
                "created_at": "2017-01-12T13:25:54+01:00",
                "name": "En Marche Paris 8",
                "slug": "en-marche-paris-8"
            },
            "status": "PENDING",
            "with_committee": true,
            "votes_count": 21,
            "author_category": "COMMITTEE",
            "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec maximus convallis dolor, id ultricies lorem lobortis et. Vivamus bibendum leo et ullamcorper dapibus.",
            "created_at": "@string@.isDateTime()",
            "name": "Faire la paix",
            "slug": "faire-la-paix",
            "days_before_deadline": "@integer@",
            "contributors_count": 0,
            "comments_count": 4
        },
        {
            "theme": {
                "name": "Armées et défense"
            },
            "category": {
                "name": "Echelle Européenne",
                "enabled": true
            },
            "needs": [],
            "author": {
                "first_name": "Jacques",
                "last_name": "Picard"
            },
            "published_at": "2018-12-04T10:00:00+01:00",
            "committee": null,
            "status": "FINALIZED",
            "with_committee": false,
            "votes_count": 0,
            "author_category": "ADHERENT",
            "description": "In nec risus vitae lectus luctus fringilla. Suspendisse vitae enim interdum, maximus justo a, elementum lectus. Mauris et augue et magna imperdiet eleifend a nec tortor.",
            "created_at": "@string@.isDateTime()",
            "name": "Réduire le gaspillage",
            "slug": "reduire-le-gaspillage",
            "days_before_deadline": "@integer@",
            "contributors_count": 0,
            "comments_count": 0
        }
    ]
    """

