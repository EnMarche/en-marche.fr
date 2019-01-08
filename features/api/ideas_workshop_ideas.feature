@api
Feature:
  In order to see ideas
  As a non logged-in user
  I should be able to access API Ideas Workshop

  Background:
    Given I freeze the clock to "2018-12-24 09:00:00"
    And the following fixtures are loaded:
      | LoadIdeaQuestionData      |
      | LoadIdeaCategoryData      |
      | LoadIdeaNeedData          |
      | LoadIdeaThemeData         |
      | LoadIdeaData              |
      | LoadIdeaThreadCommentData |
      | LoadIdeaVoteData          |

  Scenario: As a non logged-in user I can see published ideas
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/ideas?status=FINALIZED"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 1,
            "items_per_page": 2,
            "count": 1,
            "current_page": 1,
            "last_page": 1
        },
        "items": [
            {
                "uuid": "c14937d6-fd42-465c-8419-ced37f3e6194",
                "themes": [
                    {
                        "name": "Armées et défense",
                        "thumbnail": "http://test.enmarche.code/assets/images/ideas_workshop/themes/default.png"
                    }
                ],
                "category": {
                    "name": "Echelle Européenne",
                    "enabled": true
                },
                "needs": [],
                "author": {
                    "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
                    "first_name": "Jacques",
                    "last_name": "Picard"
                },
                "published_at": "2018-12-04T10:00:00+01:00",
                "committee": null,
                "status": "FINALIZED",
                "votes_count": {
                    "total": 0,
                    "important": 0,
                    "feasible": 0,
                    "innovative": 0
                },
                "author_category": "ADHERENT",
                "description": "In nec risus vitae lectus luctus fringilla. Suspendisse vitae enim interdum, maximus justo a, elementum lectus. Mauris et augue et magna imperdiet eleifend a nec tortor.",
                "created_at": "@string@.isDateTime()",
                "name": "Réduire le gaspillage",
                "slug": "reduire-le-gaspillage",
                "days_before_deadline": 1,
                "contributors_count": 0,
                "comments_count": 0
            }
        ]
    }
    """

  Scenario: As a non logged-in user I can see pending ideas
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/ideas?status=PENDING"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 1,
            "items_per_page": 2,
            "count": 1,
            "current_page": 1,
            "last_page": 1
        },
        "items": [
            {
                "uuid": "e4ac3efc-b539-40ac-9417-b60df432bdc5",
                "themes": [
                    {
                        "name": "Armées et défense",
                        "thumbnail": "http://test.enmarche.code/assets/images/ideas_workshop/themes/default.png"
                    }
                ],
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
                    "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
                    "first_name": "Jacques",
                    "last_name": "Picard"
                },
                "published_at": "2018-12-20T10:00:00+01:00",
                "committee": {
                    "uuid": "515a56c0-bde8-56ef-b90c-4745b1c93818",
                    "created_at": "2017-01-12T13:25:54+01:00",
                    "name": "En Marche Paris 8",
                    "slug": "en-marche-paris-8"
                },
                "status": "PENDING",
                "votes_count": {
                    "total": 15,
                    "important": "6",
                    "feasible": "4",
                    "innovative": "5"
                },
                "author_category": "COMMITTEE",
                "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec maximus convallis dolor, id ultricies lorem lobortis et. Vivamus bibendum leo et ullamcorper dapibus.",
                "created_at": "@string@.isDateTime()",
                "name": "Faire la paix",
                "slug": "faire-la-paix",
                "days_before_deadline": 17,
                "contributors_count": 7,
                "comments_count": 7
            }
        ]
    }
    """

  Scenario: As a non logged-in user I can filter ideas by name
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/ideas?name=paix"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 1,
            "items_per_page": 2,
            "count": 1,
            "current_page": 1,
            "last_page": 1
        },
        "items": [
            {
                "uuid": "e4ac3efc-b539-40ac-9417-b60df432bdc5",
                "themes": [
                    {
                        "name": "Armées et défense",
                        "thumbnail": "http://test.enmarche.code/assets/images/ideas_workshop/themes/default.png"
                    }
                ],
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
                    "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
                    "first_name": "Jacques",
                    "last_name": "Picard"
                },
                "published_at": "2018-12-20T10:00:00+01:00",
                "committee": {
                    "uuid": "515a56c0-bde8-56ef-b90c-4745b1c93818",
                    "created_at": "2017-01-12T13:25:54+01:00",
                    "name": "En Marche Paris 8",
                    "slug": "en-marche-paris-8"
                },
                "status": "PENDING",
                "votes_count": {
                    "total": 15,
                    "important": "6",
                    "feasible": "4",
                    "innovative": "5"
                },
                "author_category": "COMMITTEE",
                "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec maximus convallis dolor, id ultricies lorem lobortis et. Vivamus bibendum leo et ullamcorper dapibus.",
                "created_at": "@string@.isDateTime()",
                "name": "Faire la paix",
                "slug": "faire-la-paix",
                "days_before_deadline": 17,
                "contributors_count": 7,
                "comments_count": 7
            }
        ]
    }
    """

  Scenario: As a logged-in user I can filter ideas by name
    Given I am logged as "jacques.picard@en-marche.fr"
    When I add "Accept" header equal to "application/json"
    And I send a "GET" request to "/api/ideas?name=paix"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 1,
            "items_per_page": 2,
            "count": 1,
            "current_page": 1,
            "last_page": 1
        },
        "items": [
            {
                "uuid": "e4ac3efc-b539-40ac-9417-b60df432bdc5",
                "themes": [
                    {
                        "name": "Armées et défense",
                        "thumbnail": "http://test.enmarche.code/assets/images/ideas_workshop/themes/default.png"
                    }
                ],
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
                    "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
                    "first_name": "Jacques",
                    "last_name": "Picard"
                },
                "published_at": "2018-12-20T10:00:00+01:00",
                "committee": {
                    "uuid": "515a56c0-bde8-56ef-b90c-4745b1c93818",
                    "created_at": "2017-01-12T13:25:54+01:00",
                    "name": "En Marche Paris 8",
                    "slug": "en-marche-paris-8"
                },
                "status": "PENDING",
                "votes_count": {
                    "total": 15,
                    "important": "6",
                    "feasible": "4",
                    "innovative": "5",
                    "my_votes": [
                        "feasible",
                        "important",
                        "innovative"
                    ]
                },
                "author_category": "COMMITTEE",
                "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec maximus convallis dolor, id ultricies lorem lobortis et. Vivamus bibendum leo et ullamcorper dapibus.",
                "created_at": "@string@.isDateTime()",
                "name": "Faire la paix",
                "slug": "faire-la-paix",
                "days_before_deadline": 17,
                "contributors_count": 7,
                "comments_count": 7
            }
        ]
    }
    """

  Scenario: As a non logged-in user I can filter ideas by theme
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/ideas?theme.name=defense"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 2,
            "items_per_page": 2,
            "count": 2,
            "current_page": 1,
            "last_page": 1
        },
        "items": [
            {
                "uuid": "e4ac3efc-b539-40ac-9417-b60df432bdc5",
                "themes": [
                    {
                        "name": "Armées et défense",
                        "thumbnail": "http://test.enmarche.code/assets/images/ideas_workshop/themes/default.png"
                    }
                ],
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
                    "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
                    "first_name": "Jacques",
                    "last_name": "Picard"
                },
                "published_at": "2018-12-20T10:00:00+01:00",
                "committee": {
                    "uuid": "515a56c0-bde8-56ef-b90c-4745b1c93818",
                    "created_at": "2017-01-12T13:25:54+01:00",
                    "name": "En Marche Paris 8",
                    "slug": "en-marche-paris-8"
                },
                "status": "PENDING",
                "votes_count": {
                    "total": 15,
                    "important": "6",
                    "feasible": "4",
                    "innovative": "5"
                },
                "author_category": "COMMITTEE",
                "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec maximus convallis dolor, id ultricies lorem lobortis et. Vivamus bibendum leo et ullamcorper dapibus.",
                "created_at": "@string@.isDateTime()",
                "name": "Faire la paix",
                "slug": "faire-la-paix",
                "days_before_deadline": 17,
                "contributors_count": 7,
                "comments_count": 7
            },
            {
                "uuid": "c14937d6-fd42-465c-8419-ced37f3e6194",
                "themes": [
                    {
                        "name": "Armées et défense",
                        "thumbnail": "http://test.enmarche.code/assets/images/ideas_workshop/themes/default.png"
                    }
                ],
                "category": {
                    "name": "Echelle Européenne",
                    "enabled": true
                },
                "needs": [],
                "author": {
                    "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
                    "first_name": "Jacques",
                    "last_name": "Picard"
                },
                "published_at": "2018-12-04T10:00:00+01:00",
                "committee": null,
                "status": "FINALIZED",
                "votes_count": {
                    "total": 0,
                    "important": 0,
                    "feasible": 0,
                    "innovative": 0
                },
                "author_category": "ADHERENT",
                "description": "In nec risus vitae lectus luctus fringilla. Suspendisse vitae enim interdum, maximus justo a, elementum lectus. Mauris et augue et magna imperdiet eleifend a nec tortor.",
                "created_at": "@string@.isDateTime()",
                "name": "Réduire le gaspillage",
                "slug": "reduire-le-gaspillage",
                "days_before_deadline": 1,
                "contributors_count": 0,
                "comments_count": 0
            }
        ]
    }
    """

  Scenario: As a non logged-in user I can filter ideas by author uuid
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/ideas?author.uuid=acc73b03-9743-47d8-99db-5a6c6f55ad67"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "metadata": {
            "total_items": 1,
            "items_per_page": 2,
            "count": 1,
            "current_page": 1,
            "last_page": 1
        },
        "items": [
            {
                "uuid": "aa093ce6-8b20-4d86-bfbc-91a73fe47285",
                "themes": [
                    {
                        "name": "Armées et défense",
                        "thumbnail": "http://test.enmarche.code/assets/images/ideas_workshop/themes/default.png"
                    }
                ],
                "category": {
                    "name": "Echelle Européenne",
                    "enabled": true
                },
                "needs": [],
                "author": {
                    "uuid": "acc73b03-9743-47d8-99db-5a6c6f55ad67",
                    "first_name": "Benjamin",
                    "last_name": "Duroc"
                },
                "published_at": null,
                "committee": null,
                "status": "DRAFT",
                "votes_count": {
                    "important": "6",
                    "feasible": "4",
                    "innovative": "5",
                    "total": 15
                },
                "author_category": "QG",
                "description": "Nam laoreet eros diam, vitae hendrerit libero interdum nec. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas.",
                "created_at": "@string@.isDateTime()",
                "name": "Aider les gens",
                "slug": "aider-les-gens",
                "days_before_deadline": 0,
                "contributors_count": 0,
                "comments_count": 0
            }
        ]
    }
    """

  Scenario: As a logged-in user I can add my idea only with a name
    Given I am logged as "martine.lindt@gmail.com"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/ideas" with body:
    """
    {
      "name": "Mon idée"
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "themes": [],
        "category": null,
        "needs": [],
        "author": {
            "uuid": "d4b1e7e1-ba18-42a9-ace9-316440b30fa7",
            "first_name": "Martine",
            "last_name": "Lindt"
        },
        "published_at": null,
        "committee": null,
        "status": "DRAFT",
        "votes_count": {
            "important": 0,
            "feasible": 0,
            "innovative": 0,
            "total": 0,
            "my_votes": []
        },
        "uuid": "@uuid@",
        "author_category": "ADHERENT",
        "description": null,
        "created_at": "@string@.isDateTime()",
        "name": "Mon idée",
        "slug": "mon-idee",
        "days_before_deadline": @integer@,
        "contributors_count": 0,
        "comments_count": 0
    }
    """

  Scenario: As a logged-in user I can add my idea with all datas
    Given I am logged as "jacques.picard@en-marche.fr"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/ideas" with body:
    """
    {
      "name": "Mon idée",
      "description": "Mon idée",
      "themes": [2],
      "category": 2,
      "committee": "515a56c0-bde8-56ef-b90c-4745b1c93818",
      "needs": [1,2],
      "answers":[
        {
          "question":1,
          "content":"Réponse à la question 1"
        },
        {
          "question":2,
          "content":"Réponse à la question 2"
        },
        {
          "question":3,
          "content":"Réponse à la question 3"
        }
      ]
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "name": "Mon idée",
        "themes": [
            {
                "name": "Trésorerie",
                "thumbnail": null
            }
        ],
        "category": {
            "name": "Echelle Nationale",
            "enabled": true
        },
        "needs": [
            {
                "name": "Juridique",
                "enabled": true
            },
            {
                "name": "Rédactionnel",
                "enabled": true
            }
        ],
        "author": {
            "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
            "first_name": "Jacques",
            "last_name": "Picard"
        },
        "published_at": null,
        "committee": {
            "uuid": "515a56c0-bde8-56ef-b90c-4745b1c93818",
            "created_at": "@string@.isDateTime()",
            "name": "En Marche Paris 8",
            "slug": "en-marche-paris-8"
        },
        "status": "DRAFT",
        "votes_count": {
            "important": 0,
            "feasible": 0,
            "innovative": 0,
            "total": 0,
            "my_votes": []
        },
        "author_category": "QG",
        "description": "Mon idée",
        "uuid": "@uuid@",
        "created_at": "@string@.isDateTime()",
        "slug": "mon-idee",
        "days_before_deadline": @integer@,
        "contributors_count": 0,
        "comments_count": 0
    }
    """

  Scenario: As a logged-in user I can modify my idea
    Given I am logged as "jacques.picard@en-marche.fr"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/ideas/e4ac3efc-b539-40ac-9417-b60df432bdc5" with body:
    """
    {
      "name": "Mon idée 2",
      "description": "Mon idée 2",
      "themes": [2],
      "category": 2,
      "committee": "515a56c0-bde8-56ef-b90c-4745b1c93818",
      "needs": [1,2],
      "answers":[
        {
          "id": 1,
          "question":1,
          "content":"Réponse à la question 1"
        },
        {
          "id": 2,
          "question":2,
          "content":"Réponse à la question 2"
        },
        {
          "id": 3,
          "question":3,
          "content":"Réponse à la question 3"
        }
      ]
    }
    """
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "name": "Mon idée 2",
        "themes": [
            {
                "name": "Trésorerie",
            "thumbnail": null
            }
        ],
        "category": {
            "name": "Echelle Nationale",
            "enabled": true
        },
        "needs": [
            {
                "name": "Juridique",
                "enabled": true
            },
            {
                "name": "Rédactionnel",
                "enabled": true
            }
        ],
        "author": {
            "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
            "first_name": "Jacques",
            "last_name": "Picard"
        },
        "published_at": "@string@.isDateTime()",
        "committee": {
            "uuid": "515a56c0-bde8-56ef-b90c-4745b1c93818",
            "created_at": "@string@.isDateTime()",
            "name": "En Marche Paris 8",
            "slug": "en-marche-paris-8"
        },
        "status": "PENDING",
        "votes_count": {
            "important": "6",
            "feasible": "4",
            "innovative": "5",
            "total": 15,
            "my_votes": [
                "feasible",
                "important",
                "innovative"
            ]
        },
        "author_category": "QG",
        "description": "Mon idée 2",
        "uuid": "e4ac3efc-b539-40ac-9417-b60df432bdc5",
        "created_at": "@string@.isDateTime()",
        "slug": "mon-idee-2",
        "days_before_deadline": 17,
        "contributors_count": 7,
        "comments_count": 7
    }
    """

  Scenario: As a logged-in user I can get ideas where I voted
    Given I am logged as "jacques.picard@en-marche.fr"
    And I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/ideas/my-contributions"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
       "metadata":{
          "total_items":1,
          "items_per_page":2,
          "count":1,
          "current_page":1,
          "last_page":1
       },
       "items":[
          {
             "uuid": "e4ac3efc-b539-40ac-9417-b60df432bdc5",
             "themes": [
                 {
                     "name": "Armées et défense",
                     "thumbnail": "http://test.enmarche.code/assets/images/ideas_workshop/themes/default.png"
                 }
             ],
             "category":{
                "name":"Echelle Européenne",
                "enabled":true
             },
             "needs":[
                {
                   "name":"Juridique",
                   "enabled":true
                }
             ],
             "author":{
                "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
                "first_name":"Jacques",
                "last_name":"Picard"
             },
             "published_at":"2018-12-20T10:00:00+01:00",
             "committee":{
                "uuid": "515a56c0-bde8-56ef-b90c-4745b1c93818",
                "created_at":"2017-01-12T13:25:54+01:00",
                "name":"En Marche Paris 8",
                "slug":"en-marche-paris-8"
             },
             "status":"PENDING",
             "votes_count":{
                "important":"6",
                "feasible":"4",
                "innovative":"5",
                "total":15,
                "my_votes":[
                   "feasible",
                   "important",
                   "innovative"
                ]
             },
             "author_category":"COMMITTEE",
             "description":"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec maximus convallis dolor, id ultricies lorem lobortis et. Vivamus bibendum leo et ullamcorper dapibus.",
             "created_at": "@string@.isDateTime()",
             "name":"Faire la paix",
             "slug":"faire-la-paix",
             "days_before_deadline": 17,
             "contributors_count": @integer@,
             "comments_count": @integer@
          }
       ]
    }
    """

  Scenario: As a logged-in user I can get ideas where I wrote a comment
    Given I am logged as "benjyd@aol.com"
    And I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/ideas/my-contributions"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
       "metadata": {
          "total_items": 1,
          "items_per_page": 2,
          "count": 1,
          "current_page": 1,
          "last_page": 1
       },
       "items": [
          {
             "uuid": "e4ac3efc-b539-40ac-9417-b60df432bdc5",
             "themes": [
                 {
                     "name": "Armées et défense",
                     "thumbnail": "http://test.enmarche.code/assets/images/ideas_workshop/themes/default.png"
                 }
             ],
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
             "author":{
                "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
                "first_name": "Jacques",
                "last_name": "Picard"
             },
             "published_at": "2018-12-20T10:00:00+01:00",
             "committee": {
                "uuid": "515a56c0-bde8-56ef-b90c-4745b1c93818",
                "created_at": "2017-01-12T13:25:54+01:00",
                "name": "En Marche Paris 8",
                "slug": "en-marche-paris-8"
             },
             "status": "PENDING",
             "votes_count":{
                "important": "6",
                "feasible": "4",
                "innovative": "5",
                "total": 15,
                "my_votes": [
                   "feasible",
                   "important"
                ]
             },
             "author_category": "COMMITTEE",
             "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec maximus convallis dolor, id ultricies lorem lobortis et. Vivamus bibendum leo et ullamcorper dapibus.",
             "created_at": "@string@.isDateTime()",
             "name": "Faire la paix",
             "slug": "faire-la-paix",
             "days_before_deadline": 17,
             "contributors_count": @integer@,
             "comments_count": @integer@
          }
       ]
    }
    """

  Scenario: As a non logged-in user I can not delete an idea
    When I send a "DELETE" request to "/api/ideas/e4ac3efc-b539-40ac-9417-b60df432bdc5"
    Then the response status code should be 401

  Scenario: As a logged-in user I can not delete an idea that is not mine
    When I am logged as "jacques.picard@en-marche.fr"
    And I send a "DELETE" request to "/api/ideas/aa093ce6-8b20-4d86-bfbc-91a73fe47285"
    Then the response status code should be 403

  Scenario: As a logged-in user I can delete my idea
    When I am logged as "jacques.picard@en-marche.fr"
    And I send a "DELETE" request to "/api/ideas/e4ac3efc-b539-40ac-9417-b60df432bdc5"
    Then the response status code should be 204
    And the response should be empty

  Scenario: As a non logged-in user I can not publish an idea
    When I send a "PUT" request to "/api/ideas/aa093ce6-8b20-4d86-bfbc-91a73fe47285/publish"
    Then the response status code should be 401

  Scenario: As a logged-in user I can not publish an idea that is not mine
    Given I am logged as "jacques.picard@en-marche.fr"
    When I send a "PUT" request to "/api/ideas/aa093ce6-8b20-4d86-bfbc-91a73fe47285/publish"
    Then the response status code should be 403

  Scenario: As a logged-in user I can not publish an idea that has another status than DRAFT at the moment of execution
    Given I am logged as "jacques.picard@en-marche.fr"
    When I send a "PUT" request to "/api/ideas/c14937d6-fd42-465c-8419-ced37f3e6194/publish"
    Then the response status code should be 400

  Scenario: As a logged-in user I get errors when I try to publish an invalid idea
    Given I am logged as "jacques.picard@en-marche.fr"
    And I add "Content-Type" header equal to "application/json"
    When I send a "PUT" request to "/api/ideas/9529e98c-2524-486f-a6ed-e2d707dc99ea/publish"
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "type": "https://tools.ietf.org/html/rfc2616#section-10",
        "title": "An error occurred",
        "detail": "@string@",
        "violations": [
            {
                "propertyPath": "themes",
                "message": "Pour publier votre idée, vous devez préciser au minimum 1 thème."
            },
            {
                "propertyPath": "category",
                "message": "Pour publier votre idée, sa catégorie devrait être remplie."
            },
            {
                "propertyPath": "needs",
                "message": "Pour publier votre idée, vous devez préciser au minimum 1 besoin."
            },
            {
                "propertyPath": "answers",
                "message": "Pour publier votre idée, vous devez apporter des réponses aux questions obligatoires."
            },
            {
                "propertyPath": "description",
                "message": "Pour publier votre idée, sa description ne doit pas être vide."
            }
        ]
    }
    """

  Scenario: As a logged-in user I can publish my idea which is in DRAFT state
    Given I am logged as "benjyd@aol.com"
    And I add "Content-Type" header equal to "application/json"
    When I send a "PUT" request to "/api/ideas/aa093ce6-8b20-4d86-bfbc-91a73fe47285" with body:
    """
    {
      "needs": [1,2],
      "answers": [
        {
          "question":1,
          "content":"Réponse à la question 1"
        },
        {
          "question":2,
          "content":"Réponse à la question 2"
        }
      ]
    }
    """
    Then the response status code should be 200
    Given I add "Content-Type" header equal to "application/json"
    When I send a "PUT" request to "/api/ideas/aa093ce6-8b20-4d86-bfbc-91a73fe47285/publish"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON nodes should contain:
      | name   | Aider les gens |
      | status | PENDING        |

  Scenario: As a logged-in user I can get full information about one idea
    Given I am logged as "benjyd@aol.com"
    And I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/ideas/e4ac3efc-b539-40ac-9417-b60df432bdc5"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
       "name":"Faire la paix",
       "themes":[
          {
             "id":1,
             "thumbnail":"http:\/\/test.enmarche.code\/assets\/images\/ideas_workshop\/themes\/default.png"
          }
       ],
       "category":{
          "id":1
       },
       "needs":[
          {
             "id":1
          }
       ],
       "author":{
          "uuid":"a046adbe-9c7b-56a9-a676-6151a6785dda",
          "first_name":"Jacques",
          "last_name":"Picard"
       },
       "published_at":"2018-12-20T10:00:00+01:00",
       "status":"PENDING",
       "answers":[
          {
             "id":3,
             "content":"Mauris gravida semper tincidunt.",
             "question":{
                "id":3
             },
             "threads":{
                "total_items":3,
                "items":[
                   {
                      "comments":{
                         "total_items":3,
                         "items":[
                            {
                               "uuid":"001a53d0-1134-429c-8dc1-c57643b3f069",
                               "content":"Commentaire refus\u00e9",
                               "author":{
                                  "uuid":"93de5d98-383a-4863-9f47-eb7a348873a8",
                                  "first_name":"Laura",
                                  "last_name":"Deloche"
                               },
                               "created_at": "@string@.isDateTime()"
                            },
                            {
                               "uuid":"3fa38c45-1122-4c48-9ada-b366b3408fec",
                               "content":"Commentaire signal\u00e9",
                               "author":{
                                  "uuid":"93de5d98-383a-4863-9f47-eb7a348873a8",
                                  "first_name":"Laura",
                                  "last_name":"Deloche"
                               },
                               "created_at": "@string@.isDateTime()"
                            },
                            {
                               "uuid":"02bf299f-678a-4829-a6a1-241995339d8d",
                               "content":"Commentaire de Laura",
                               "author":{
                                  "uuid":"93de5d98-383a-4863-9f47-eb7a348873a8",
                                  "first_name":"Laura",
                                  "last_name":"Deloche"
                               },
                               "created_at": "@string@.isDateTime()"
                            }
                         ]
                      },
                      "uuid":"a508a7c5-8b07-41f4-8515-064f674a65e8",
                      "content":"J'ouvre une discussion sur la comparaison.",
                      "author":{
                         "uuid":"b4219d47-3138-5efd-9762-2ef9f9495084",
                         "first_name":"Gisele",
                         "last_name":"Berthoux"
                      },
                      "created_at": "@string@.isDateTime()"
                   },
                   {
                      "comments":{
                         "total_items":0,
                         "items":[

                         ]
                      },
                      "uuid":"78d7daa1-657c-4e7e-87bc-24eb4ea26ea2",
                      "content":"Une discussion refus\u00e9e.",
                      "author":{
                         "uuid":"b4219d47-3138-5efd-9762-2ef9f9495084",
                         "first_name":"Gisele",
                         "last_name":"Berthoux"
                      },
                      "created_at": "@string@.isDateTime()"
                   },
                   {
                      "comments":{
                         "total_items":0,
                         "items":[

                         ]
                      },
                      "uuid":"b191f13a-5a05-49ed-8ec3-c335aa68f439",
                      "content":"Une discussion signal\u00e9e.",
                      "author":{
                         "uuid":"b4219d47-3138-5efd-9762-2ef9f9495084",
                         "first_name":"Gisele",
                         "last_name":"Berthoux"
                      },
                      "created_at": "@string@.isDateTime()"
                   }
                ]
             }
          },
          {
             "id":1,
             "content":"Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce aliquet, mi condimentum venenatis vestibulum, arcu neque feugiat massa, at pharetra velit sapien et elit. Sed vitae hendrerit nulla. Vivamus consectetur magna at tincidunt maximus. Aenean dictum metus vel tellus posuere venenatis.",
             "question":{
                "id":1
             },
             "threads":{
                "total_items":1,
                "items":[
                   {
                      "comments":{
                         "total_items":4,
                         "items":[
                            {
                               "uuid":"ecbe9136-3dc0-477d-b817-a25878dd639a",
                               "content":"Deuxi\u00e8me commentaire d'un r\u00e9f\u00e9rent",
                               "author":{
                                  "uuid":"29461c49-2646-4d89-9c82-50b3f9b586f4",
                                  "first_name":"Referent",
                                  "last_name":"Referent"
                               },
                               "created_at": "@string@.isDateTime()"
                            },
                            {
                               "uuid":"f716d3ba-004f-4958-af26-a7b010a6d458",
                               "content":"Commentaire d'un r\u00e9f\u00e9rent",
                               "author":{
                                  "uuid":"29461c49-2646-4d89-9c82-50b3f9b586f4",
                                  "first_name":"Referent",
                                  "last_name":"Referent"
                               },
                               "created_at": "@string@.isDateTime()"
                            },
                            {
                               "uuid":"60123090-6cdc-4de6-9cb3-07e2ec411f2f",
                               "content":"Lorem Ipsum Commentaris",
                               "author":{
                                  "uuid":"a9fc8d48-6f57-4d89-ae73-50b3f9b586f4",
                                  "first_name":"Francis",
                                  "last_name":"Brioul"
                               },
                               "created_at": "@string@.isDateTime()"
                            }
                         ]
                      },
                      "uuid":"dfd6a2f2-5579-421f-96ac-98993d0edea1",
                      "content":"J'ouvre une discussion sur le probl\u00e8me.",
                      "author":{
                         "uuid":"e6977a4d-2646-5f6c-9c82-88e58dca8458",
                         "first_name":"Carl",
                         "last_name":"Mirabeau"
                      },
                      "created_at": "@string@.isDateTime()"
                   }
                ]
             }
          },
          {
             "id":2,
             "content":"Nulla metus enim, congue eu facilisis ac, consectetur ut ipsum. ",
             "question":{
                "id":2
             },
             "threads":{
                "total_items":1,
                "items":[
                   {
                      "comments":{
                         "total_items":0,
                         "items":[

                         ]
                      },
                      "uuid":"6b077cc4-1cbd-4615-b607-c23009119406",
                      "content":"J'ouvre une discussion sur la solution.",
                      "author":{
                         "uuid":"29461c49-6316-5be1-9ac3-17816bf2d819",
                         "first_name":"Lucie",
                         "last_name":"Olivera"
                      },
                      "created_at": "@string@.isDateTime()"
                   }
                ]
             }
          },
          {
             "id":4,
             "content":"Donec ac neque congue, condimentum ipsum ac, eleifend ex.",
             "question":{
                "id":4
             },
             "threads":{
                "total_items":0,
                "items":[

                ]
             }
          },
          {
             "id":5,
             "content":"Suspendisse interdum quis tortor quis sodales. Suspendisse vel mollis orci.",
             "question":{
                "id":5
             },
             "threads":{
                "total_items":0,
                "items":[

                ]
             }
          },
          {
             "id":6,
             "content":"Proin et quam a tortor pretium fringilla non et magna.",
             "question":{
                "id":6
             },
             "threads":{
                "total_items":0,
                "items":[

                ]
             }
          },
          {
             "id":7,
             "content":"Orci varius natoque penatibus et magnis dis parturient montes",
             "question":{
                "id":7
             },
             "threads":{
                "total_items":0,
                "items":[

                ]
             }
          },
          {
             "id":8,
             "content":"Nam nisi nunc, ornare nec elit id, porttitor vestibulum ligula. Donec enim tellus, congue non quam at, aliquam porta ex.",
             "question":{
                "id":8
             },
             "threads":{
                "total_items":0,
                "items":[

                ]
             }
          }
       ],
       "votes_count":{
          "important":"6",
          "feasible":"4",
          "innovative":"5",
          "total":15,
          "my_votes":[
             "feasible",
             "important"
          ]
       },
       "created_at": "@string@.isDateTime()"
    }
    """

  Scenario: As a non logged-in user I can get full information about one idea
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/ideas/c14937d6-fd42-465c-8419-ced37f3e6194"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
       "name":"Réduire le gaspillage",
       "themes":[
          {
             "id":1,
             "thumbnail":"http:\/\/test.enmarche.code\/assets\/images\/ideas_workshop\/themes\/default.png"
          }
       ],
       "category":{
          "id":1
       },
       "needs":[

       ],
       "author":{
          "uuid":"a046adbe-9c7b-56a9-a676-6151a6785dda",
          "first_name":"Jacques",
          "last_name":"Picard"
       },
       "published_at":"2018-12-04T10:00:00+01:00",
       "status":"FINALIZED",
       "answers":[

       ],
       "votes_count":{
          "important":0,
          "feasible":0,
          "innovative":0,
          "total":0
       },
       "created_at": "@string@.isDateTime()"
    }
    """
