@api
Feature:
  In order to see causes
  As a non logged-in user
  I should be able to access API causes

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData |
      | LoadClientData   |
      | LoadCauseData    |
      | LoadGeoZoneData  |

  Scenario: As a non logged-in user I can get causes statistics
    Given I send a "GET" request to "/api/causes/statistiques"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
      "total": "6",
      "total_followers": "5"
    }
    """

  Scenario: As a non logged-in user I can see first page of active causes
    Given I send a "GET" request to "/api/causes"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
      "metadata": {
        "total_items": 5,
        "items_per_page": 2,
        "count": 2,
        "current_page": 1,
        "last_page": 3
      },
      "items": [
        {
          "name": "Cause pour la culture",
          "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
          "coalition": {
            "name": "Culture",
            "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315",
            "followers_count": 4
          },
          "second_coalition": {
            "name": "Éducation",
            "uuid": "fff11d8d-5cb5-4075-b594-fea265438d65",
            "followers_count": 0
          },
          "followers_count": 5,
          "slug": "cause-pour-la-culture",
          "uuid": "55056e7c-2b5f-4ef6-880e-cde0511f79b2",
          "author": {
            "uuid": "e6977a4d-2646-5f6c-9c82-88e58dca8458",
            "first_name": "Carl",
            "last_name": "Mirabeau",
            "last_name_initial": "M."
          },
          "image_url": "http://test.enmarche.code/assets/images/causes/644d1c64512ab5489ab8590a3b313517.png"
        },
        {
          "name": "Cause pour l'éducation",
          "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
          "coalition": {
            "name": "Éducation",
            "uuid": "fff11d8d-5cb5-4075-b594-fea265438d65",
            "followers_count": 0
          },
          "second_coalition": null,
          "followers_count": 0,
          "slug": "cause-pour-leducation",
          "uuid": "fa6bd29c-48b7-490e-90fb-48ab5fb2ddf8",
          "author": {
            "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
            "first_name": "Jacques",
            "last_name": "Picard",
            "last_name_initial": "P."
          },
          "image_url": "http://test.enmarche.code/assets/images/causes/532c52e162feb2f6cfae99d5ed52d41f.png"
        }
      ]
    }
    """

  Scenario: As a non logged-in user I can paginate causes with default number of causes by page
    Given I send a "GET" request to "/api/causes?page=2"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "metadata": {
        "total_items": 5,
        "items_per_page": 2,
        "count": 2,
        "current_page": 2,
        "last_page": 3
      },
      "items": [
        {
          "name": "Cause pour la culture 2",
          "slug": "cause-pour-la-culture-2",
          "description": "Description de la cause pour la culture 2",
          "coalition": {
            "name": "Culture",
            "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315",
            "followers_count": 4
          },
          "second_coalition": {
            "name": "Éducation",
            "uuid": "fff11d8d-5cb5-4075-b594-fea265438d65",
            "followers_count": 0
          },
          "uuid": "017491f9-1953-482e-b491-20418235af1f",
          "author": {
            "uuid": "e6977a4d-2646-5f6c-9c82-88e58dca8458",
            "first_name": "Carl",
            "last_name": "Mirabeau",
            "last_name_initial": "M."
          },
          "followers_count": 0,
          "image_url": "http://test.enmarche.code/assets/images/causes/73a6283e0b639cbeb50b9b28d401eaca.png"
        },
        {
          "name": "Cause pour la culture 3",
          "slug": "cause-pour-la-culture-3",
          "description": "Description de la cause pour la culture 3",
          "coalition": {
            "name": "Culture",
            "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315",
            "followers_count": 4
          },
          "second_coalition": null,
          "uuid": "5f8a6d40-9e69-4311-a45b-67c00d30ad41",
          "author": {
            "uuid": "e6977a4d-2646-5f6c-9c82-88e58dca8458",
            "first_name": "Carl",
            "last_name": "Mirabeau",
            "last_name_initial": "M."
          },
          "followers_count": 0,
          "image_url": null
        }
      ]
    }
    """

  Scenario: As a non logged-in user I can paginate causes with specific number of causes by page
    Given I send a "GET" request to "/api/causes?page_size=5"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "metadata": {
        "total_items": 5,
        "items_per_page": 5,
        "count": 5,
        "current_page": 1,
        "last_page": 1
      },
      "items": [
        {
          "name": "Cause pour la culture",
          "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
          "coalition": {
            "name": "Culture",
            "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315",
            "followers_count": 4
          },
          "second_coalition": {
            "name": "Éducation",
            "uuid": "fff11d8d-5cb5-4075-b594-fea265438d65",
            "followers_count": 0
          },
          "followers_count": 5,
          "slug": "cause-pour-la-culture",
          "uuid": "55056e7c-2b5f-4ef6-880e-cde0511f79b2",
          "author": {
            "uuid": "e6977a4d-2646-5f6c-9c82-88e58dca8458",
            "first_name": "Carl",
            "last_name": "Mirabeau",
            "last_name_initial": "M."
          },
          "image_url": "http://test.enmarche.code/assets/images/causes/644d1c64512ab5489ab8590a3b313517.png"
        },
        {
          "name": "Cause pour l'éducation",
          "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
          "coalition": {
            "name": "Éducation",
            "uuid": "fff11d8d-5cb5-4075-b594-fea265438d65",
            "followers_count": 0
          },
          "second_coalition": null,
          "followers_count": 0,
          "slug": "cause-pour-leducation",
          "uuid": "fa6bd29c-48b7-490e-90fb-48ab5fb2ddf8",
          "author": {
            "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
            "first_name": "Jacques",
            "last_name": "Picard",
            "last_name_initial": "P."
          },
          "image_url": "http://test.enmarche.code/assets/images/causes/532c52e162feb2f6cfae99d5ed52d41f.png"
        },
        {
          "name": "Cause pour la culture 2",
          "description": "Description de la cause pour la culture 2",
          "coalition": {
            "name": "Culture",
            "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315",
            "followers_count": 4
          },
          "second_coalition": {
            "name": "Éducation",
            "uuid": "fff11d8d-5cb5-4075-b594-fea265438d65",
            "followers_count": 0
          },
          "followers_count": 0,
          "slug": "cause-pour-la-culture-2",
          "uuid": "017491f9-1953-482e-b491-20418235af1f",
          "author": {
            "uuid": "e6977a4d-2646-5f6c-9c82-88e58dca8458",
            "first_name": "Carl",
            "last_name": "Mirabeau",
            "last_name_initial": "M."
          },
          "image_url": "http://test.enmarche.code/assets/images/causes/73a6283e0b639cbeb50b9b28d401eaca.png"
        },
        {
          "name": "Cause pour la culture 3",
          "description": "Description de la cause pour la culture 3",
          "coalition": {
            "name": "Culture",
            "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315",
            "followers_count": 4
          },
          "second_coalition": null,
          "followers_count": 0,
          "slug": "cause-pour-la-culture-3",
          "uuid": "5f8a6d40-9e69-4311-a45b-67c00d30ad41",
          "author": {
            "uuid": "e6977a4d-2646-5f6c-9c82-88e58dca8458",
            "first_name": "Carl",
            "last_name": "Mirabeau",
            "last_name_initial": "M."
          },
          "image_url": null
        },
        {
          "name": "Cause pour la justice",
          "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
          "coalition": {
            "name": "Justice",
            "uuid": "5b8db218-4da6-4f7f-a53e-29a7a349d45c",
            "followers_count": 0
          },
          "second_coalition": null,
          "followers_count": 0,
          "slug": "cause-pour-la-justice",
          "uuid": "44249b1d-ea10-41e0-b288-5eb74fa886ba",
          "author": {
            "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
            "first_name": "Jacques",
            "last_name": "Picard",
            "last_name_initial": "P."
          },
          "image_url": null
        }
      ]
    }
    """

  Scenario: As a non logged-in user I can filter causes by a list of exact coalition uuids
    Given I send a "GET" request to "/api/causes?coalition.uuid[]=fff11d8d-5cb5-4075-b594-fea265438d65"
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
          "name": "Cause pour l'éducation",
          "slug": "cause-pour-leducation",
          "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
          "coalition": {
            "name": "Éducation",
            "uuid": "fff11d8d-5cb5-4075-b594-fea265438d65",
            "followers_count": 0
          },
          "second_coalition": null,
          "uuid": "fa6bd29c-48b7-490e-90fb-48ab5fb2ddf8",
          "author": {
            "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
            "first_name": "Jacques",
            "last_name": "Picard",
            "last_name_initial": "P."
          },
          "followers_count": 0,
          "image_url": "http://test.enmarche.code/assets/images/causes/532c52e162feb2f6cfae99d5ed52d41f.png"
        }
      ]
    }
    """
    Given I send a "GET" request to "/api/causes?coalition.uuid[]=fff11d8d-5cb5-4075-b594-fea265438d65&coalition.uuid[]=d5289058-2a35-4cf0-8f2f-a683d97d8315"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "metadata": {
        "total_items": 4,
        "items_per_page": 2,
        "count": 2,
        "current_page": 1,
        "last_page": 2
      },
      "items": [
        {
          "name": "Cause pour la culture",
          "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
          "coalition": {
            "name": "Culture",
            "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315",
            "followers_count": 4
          },
          "second_coalition": {
            "name": "Éducation",
            "uuid": "fff11d8d-5cb5-4075-b594-fea265438d65",
            "followers_count": 0
          },
          "followers_count": 5,
          "slug": "cause-pour-la-culture",
          "uuid": "55056e7c-2b5f-4ef6-880e-cde0511f79b2",
          "author": {
            "uuid": "e6977a4d-2646-5f6c-9c82-88e58dca8458",
            "first_name": "Carl",
            "last_name": "Mirabeau",
            "last_name_initial": "M."
          },
          "image_url": "http://test.enmarche.code/assets/images/causes/644d1c64512ab5489ab8590a3b313517.png"
        },
        {
          "name": "Cause pour l'éducation",
          "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
          "coalition": {
            "name": "Éducation",
            "uuid": "fff11d8d-5cb5-4075-b594-fea265438d65",
            "followers_count": 0
          },
          "second_coalition": null,
          "followers_count": 0,
          "slug": "cause-pour-leducation",
          "uuid": "fa6bd29c-48b7-490e-90fb-48ab5fb2ddf8",
          "author": {
            "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
            "first_name": "Jacques",
            "last_name": "Picard",
            "last_name_initial": "P."
          },
          "image_url": "http://test.enmarche.code/assets/images/causes/532c52e162feb2f6cfae99d5ed52d41f.png"
        }
      ]
    }
    """

  Scenario: As a non logged-in user I can get one cause by uuid
    Given I send a "GET" request to "/api/causes/fa6bd29c-48b7-490e-90fb-48ab5fb2ddf8"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
      {
        "author": {
          "first_name": "Jacques",
            "last_name": "Picard",
          "last_name_initial": "P.",
          "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda"
        },
        "coalition": {
          "name": "Éducation",
          "uuid": "fff11d8d-5cb5-4075-b594-fea265438d65",
          "followers_count": 0
        },
        "second_coalition": null,
        "name": "Cause pour l'éducation",
        "slug": "cause-pour-leducation",
        "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
        "uuid": "fa6bd29c-48b7-490e-90fb-48ab5fb2ddf8",
        "followers_count": 0,
        "image_url": "http://test.enmarche.code/assets/images/causes/532c52e162feb2f6cfae99d5ed52d41f.png"
      }
    """

  Scenario: As a non logged-in user I can get one cause by his slug
    Given I send a "GET" request to "/api/causes/cause-pour-leducation"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
      {
        "author": {
          "first_name": "Jacques",
            "last_name": "Picard",
          "last_name_initial": "P.",
          "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda"
        },
        "coalition": {
          "name": "Éducation",
          "uuid": "fff11d8d-5cb5-4075-b594-fea265438d65",
          "followers_count": 0
        },
        "second_coalition": null,
        "name": "Cause pour l'éducation",
        "slug": "cause-pour-leducation",
        "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
        "uuid": "fa6bd29c-48b7-490e-90fb-48ab5fb2ddf8",
        "followers_count": 0,
        "image_url": "http://test.enmarche.code/assets/images/causes/532c52e162feb2f6cfae99d5ed52d41f.png"
      }
    """

  Scenario: As a non logged-in user I can not get a refused cause
    Given I send a "GET" request to "/api/causes/227a2360-f4b3-4e3f-8f85-5b1700300ca9"
    Then the response status code should be 404

  Scenario: As a non logged-in user I get a 404 when providing an unknown cause uuid
    Given I send a "GET" request to "/api/causes/70126dd9-4a9e-4dbe-8093-14be4a24b9ed"
    Then the response status code should be 404

  Scenario: As a non logged-in user I can get causes of some coalition
    Given I send a "GET" request to "/api/coalitions/d5289058-2a35-4cf0-8f2f-a683d97d8315/causes"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "metadata": {
        "total_items": 3,
        "items_per_page": 2,
        "count": 2,
        "current_page": 1,
        "last_page": 2
      },
      "items": [
        {
          "name": "Cause pour la culture",
          "slug": "cause-pour-la-culture",
          "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
          "coalition": {
            "name": "Culture",
            "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315",
            "followers_count": 4
          },
          "second_coalition": {
            "name": "Éducation",
            "uuid": "fff11d8d-5cb5-4075-b594-fea265438d65",
            "followers_count": 0
          },
          "uuid": "55056e7c-2b5f-4ef6-880e-cde0511f79b2",
          "author": {
            "uuid": "e6977a4d-2646-5f6c-9c82-88e58dca8458",
            "first_name": "Carl",
            "last_name": "Mirabeau",
            "last_name_initial": "M."
          },
          "followers_count": 5,
          "image_url": "http://test.enmarche.code/assets/images/causes/644d1c64512ab5489ab8590a3b313517.png"
        },
        {
          "name": "Cause pour la culture 2",
          "slug": "cause-pour-la-culture-2",
          "description": "Description de la cause pour la culture 2",
          "coalition": {
            "name": "Culture",
            "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315",
            "followers_count": 4
          },
          "second_coalition": {
            "name": "Éducation",
            "uuid": "fff11d8d-5cb5-4075-b594-fea265438d65",
            "followers_count": 0
          },
          "uuid": "017491f9-1953-482e-b491-20418235af1f",
          "author": {
            "uuid": "e6977a4d-2646-5f6c-9c82-88e58dca8458",
            "first_name": "Carl",
            "last_name": "Mirabeau",
            "last_name_initial": "M."
          },
          "followers_count": 0,
          "image_url": "http://test.enmarche.code/assets/images/causes/73a6283e0b639cbeb50b9b28d401eaca.png"
        }
      ]
    }
    """

  Scenario: As a non logged-in user I can paginate causes of some coalition
    Given I send a "GET" request to "/api/coalitions/d5289058-2a35-4cf0-8f2f-a683d97d8315/causes?page=2"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "metadata": {
        "total_items": 3,
        "items_per_page": 2,
        "count": 1,
        "current_page": 2,
        "last_page": 2
      },
      "items": [
        {
          "name": "Cause pour la culture 3",
          "slug": "cause-pour-la-culture-3",
          "description": "Description de la cause pour la culture 3",
          "coalition": {
            "name": "Culture",
            "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315",
            "followers_count": 4
          },
          "second_coalition": null,
          "uuid": "5f8a6d40-9e69-4311-a45b-67c00d30ad41",
          "author": {
            "uuid": "e6977a4d-2646-5f6c-9c82-88e58dca8458",
            "first_name": "Carl",
            "last_name": "Mirabeau",
            "last_name_initial": "M."
          },
          "followers_count": 0,
          "image_url": null
        }
      ]
    }
    """

  Scenario Outline: As a non logged-in user I can not follow/unfollow a cause as an adherent
    Given I add "Accept" header equal to "application/json"
    When I send a "<method>" request to "<url>"
    Then the response status code should be 401
    Examples:
      | method | url                                                          |
      | PUT    | /api/v3/causes/55056e7c-2b5f-4ef6-880e-cde0511f79b2/follower |
      | DELETE | /api/v3/causes/55056e7c-2b5f-4ef6-880e-cde0511f79b2/follower |

  Scenario: As a logged-in user I can follow a cause (without CoalitionSubscription)
    When I send a "GET" request to "/api/causes/55056e7c-2b5f-4ef6-880e-cde0511f79b2"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON nodes should match:
      | followers_count           | 5 |
      | coalition.followers_count | 4 |
    When I am logged with "gisele-berthoux@caramail.com" via OAuth client "Coalition App"
    And I send a "PUT" request to "/api/v3/causes/55056e7c-2b5f-4ef6-880e-cde0511f79b2/follower"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "uuid": "@string@"
    }
    """
    And I should have 1 email "CauseFollowerConfirmationMessage" for "gisele-berthoux@caramail.com" with payload:
    """
    {
        "template_name": "cause-follower-confirmation",
        "template_content": [],
        "message": {
            "subject": "L'aventure débute maintenant !",
            "from_email": "contact@pourunecause.fr",
            "global_merge_vars": [
                {
                    "name": "first_name",
                    "content": "Gisele"
                },
                {
                    "name": "author_first_name",
                    "content": "Carl"
                },
                {
                    "name": "cause_name",
                    "content": "Cause pour la culture"
                },
                {
                    "name": "cause_link",
                    "content": "http://coalitions.code/cause/55056e7c-2b5f-4ef6-880e-cde0511f79b2"
                }
            ],
            "from_name": "Pour une cause",
            "to": [
                {
                    "email": "gisele-berthoux@caramail.com",
                    "type": "to",
                    "name": "Gisele Berthoux"
                }
            ]
        }
    }
    """
    When I log out
    And I send a "GET" request to "/api/causes/55056e7c-2b5f-4ef6-880e-cde0511f79b2"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON nodes should match:
      | followers_count           | 6 |
      | coalition.followers_count | 4 |

  Scenario: As a logged-in user I can follow a cause (with CoalitionSubscription)
    When I send a "GET" request to "/api/causes/55056e7c-2b5f-4ef6-880e-cde0511f79b2"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON nodes should match:
      | followers_count           | 5 |
      | coalition.followers_count | 4 |

    When I am logged with "benjyd@aol.com" via OAuth client "Coalition App"
    And I send a "PUT" request to "/api/v3/causes/55056e7c-2b5f-4ef6-880e-cde0511f79b2/follower"
    Then the response status code should be 200

    When I log out
    And I send a "GET" request to "/api/causes/55056e7c-2b5f-4ef6-880e-cde0511f79b2"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON nodes should match:
      | followers_count           | 6 |
      | coalition.followers_count | 5 |

  Scenario: As a logged-in user I can unfollow a cause
    When I send a "GET" request to "/api/causes/55056e7c-2b5f-4ef6-880e-cde0511f79b2"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON nodes should match:
      | followers_count           | 5 |
      | coalition.followers_count | 4 |

    When I am logged with "jacques.picard@en-marche.fr" via OAuth client "Coalition App"
    And I send a "DELETE" request to "/api/v3/causes/55056e7c-2b5f-4ef6-880e-cde0511f79b2/follower"
    Then the response status code should be 204

    When I log out
    And I send a "GET" request to "/api/causes/55056e7c-2b5f-4ef6-880e-cde0511f79b2"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON nodes should match:
      | followers_count           | 4 |
      | coalition.followers_count | 4 |

  Scenario: As a logged-in user I can check if I follow causes
    Given I am logged with "carl999@example.fr" via OAuth client "Coalition App"
    When I send a "POST" request to "/api/v3/causes/followed" with body:
    """
    {
      "uuids": [
        "55056e7c-2b5f-4ef6-880e-cde0511f79b2",
        "44249b1d-ea10-41e0-b288-5eb74fa886ba"
      ]
    }
    """
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    [
      "55056e7c-2b5f-4ef6-880e-cde0511f79b2"
    ]
    """

  Scenario: As a non logged-in user I can not check if I follow causes
    When I send a "POST" request to "/api/v3/causes/followed" with parameters:
      | key     | value                                |
      | uuids[] | 55056e7c-2b5f-4ef6-880e-cde0511f79b2 |
      | uuids[] | 44249b1d-ea10-41e0-b288-5eb74fa886ba |
    Then the response status code should be 401

  Scenario: As a non logged-in user I can follow a cause
    When I send a "GET" request to "/api/causes/017491f9-1953-482e-b491-20418235af1f"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON nodes should match:
      | followers_count | 0 |

    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/causes/017491f9-1953-482e-b491-20418235af1f/follower" with body:
    """
    {
        "email_address": "adherent@en-marche-dev.fr",
        "first_name": "Pierre",
        "zone": "e3f274b8-906e-11eb-a875-0242ac150002",
        "cgu_accepted": true,
        "cause_subscription": true,
        "coalition_subscription": true
    }
    """
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
      "OK"
    """
    And I should have 1 email "CauseFollowerAnonymousConfirmationMessage" for "adherent@en-marche-dev.fr" with payload:
    """
    {
        "template_name": "cause-follower-anonymous-confirmation",
        "template_content": [],
        "message": {
            "subject": "L'aventure débute maintenant !",
            "from_email": "contact@pourunecause.fr",
            "global_merge_vars": [
                {
                    "name": "first_name",
                    "content": "Pierre"
                },
                {
                    "name": "author_first_name",
                    "content": "Carl"
                },
                {
                    "name": "cause_name",
                    "content": "Cause pour la culture 2"
                },
                {
                    "name": "cause_link",
                    "content": "http://coalitions.code/cause/017491f9-1953-482e-b491-20418235af1f"
                },
                {
                    "name": "create_account_link",
                    "content": "http://coalitions.code/inscription"
                }
            ],
            "from_name": "Pour une cause",
            "to": [
                {
                    "email": "adherent@en-marche-dev.fr",
                    "type": "to",
                    "name": "Pierre"
                }
            ]
        }
    }
    """
    When I send a "GET" request to "/api/causes/017491f9-1953-482e-b491-20418235af1f"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON nodes should match:
      | followers_count | 1 |

  Scenario: As a non logged-in user I can not follow a cause if no all required data
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/causes/017491f9-1953-482e-b491-20418235af1f/follower" with body:
    """
    {}
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "type": "https://tools.ietf.org/html/rfc2616#section-10",
        "title": "An error occurred",
        "detail": "first_name: Cette valeur ne doit pas être vide.\nemail_address: Cette valeur ne doit pas être vide.\nzone: Cette valeur ne doit pas être vide.\ncgu_accepted: Cette valeur ne doit pas être vide.",
        "violations": [
            {
                "code": "@uuid@",
                "propertyPath": "first_name",
                "message": "Cette valeur ne doit pas être vide."
            },
            {
                "code": "@uuid@",
                "propertyPath": "email_address",
                "message": "Cette valeur ne doit pas être vide."
            },
            {
                "code": "@uuid@",
                "propertyPath": "zone",
                "message": "Cette valeur ne doit pas être vide."
            },
            {
                "code": "@uuid@",
                "propertyPath": "cgu_accepted",
                "message": "Cette valeur ne doit pas être vide."
            }
        ]
    }
    """

  Scenario: As a non logged-in user I can not follow a cause that I am already follow
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/causes/55056e7c-2b5f-4ef6-880e-cde0511f79b2/follower" with body:
    """
    {
        "email_address": "adherent@en-marche-dev.fr",
        "first_name": "Pierre",
        "zone": "e3f274b8-906e-11eb-a875-0242ac150002",
        "cause": "55056e7c-2b5f-4ef6-880e-cde0511f79b2",
        "cgu_accepted": true
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "type": "https://tools.ietf.org/html/rfc2616#section-10",
        "title": "An error occurred",
        "detail": "email_address: Vous avez déjà soutenu cette cause.",
        "violations": [
            {
                "code": "@uuid@",
                "propertyPath": "email_address",
                "message": "Vous avez déjà soutenu cette cause."
            }
        ]
    }
    """

  Scenario: As a non logged-in user I can not follow a cause if I have already an account
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/causes/017491f9-1953-482e-b491-20418235af1f/follower" with body:
    """
    {
        "email_address": "jacques.picard@en-marche.fr",
        "first_name": "jacques",
        "zone": "e3f274b8-906e-11eb-a875-0242ac150002",
        "cause": "017491f9-1953-482e-b491-20418235af1f",
        "cgu_accepted": true
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
        "type": "https://tools.ietf.org/html/rfc2616#section-10",
        "title": "An error occurred",
        "detail": "email_address: L'utilisateur avec cette adresse e-mail existe déjà. Veuillez vous connecter pour soutenir la cause.",
        "violations": [
            {
                "code": null,
                "propertyPath": "email_address",
                "message": "L'utilisateur avec cette adresse e-mail existe déjà. Veuillez vous connecter pour soutenir la cause."
            }
        ]
    }
    """

  Scenario: As a logged-in user I can create a cause
    When I send a "GET" request to "/api/coalitions/d5289058-2a35-4cf0-8f2f-a683d97d8315"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON nodes should match:
      | followers_count | 4 |

    When I am logged with "benjyd@aol.com" via OAuth client "Coalition App"
    And I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/causes" with body:
    """
    {
      "name": "Nouvelle cause sur la culture",
      "description": "Description de la nouvelle cause sur la culture",
      "coalition": "d5289058-2a35-4cf0-8f2f-a683d97d8315"
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON nodes should match:
      | name                      | Nouvelle cause sur la culture                   |
      | description               | Description de la nouvelle cause sur la culture |
      | coalition.uuid            | d5289058-2a35-4cf0-8f2f-a683d97d8315            |
      | followers_count           | 1                                               |
      | coalition.followers_count | 5                                               |
    And I should have 1 email "CauseCreationConfirmationMessage" for "benjyd@aol.com" with payload:
    """
    {
        "template_name": "cause-creation-confirmation",
        "template_content": [],
        "message": {
            "subject": "Votre cause sera très vite en ligne !",
            "from_email": "contact@pourunecause.fr",
            "global_merge_vars": [
                {
                    "name": "first_name",
                    "content": "Benjamin"
                },
                {
                    "name": "cause_name",
                    "content": "Nouvelle cause sur la culture"
                },
                {
                    "name": "cause_list_link",
                    "content": "http://coalitions.code/causes"
                }
            ],
            "from_name": "Pour une cause",
            "to": [
                {
                    "email": "benjyd@aol.com",
                    "type": "to",
                    "name": "Benjamin Duroc"
                }
            ]
        }
    }
    """

  Scenario: As a logged-in user I can create a cause with second coalition
    Given I am logged with "gisele-berthoux@caramail.com" via OAuth client "Coalition App"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/causes" with body:
    """
    {
      "name": "Nouvelle cause sur la culture 2",
      "description": "",
      "coalition": "d5289058-2a35-4cf0-8f2f-a683d97d8315",
      "second_coalition": "09d700f8-8813-4c3c-9bee-ff18d2051bba"
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "name": "Nouvelle cause sur la culture 2",
      "description": "",
      "coalition": {
        "name": "Culture",
        "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315",
        "followers_count": 4
      },
      "second_coalition": {
        "name": "Démocratie",
        "uuid": "09d700f8-8813-4c3c-9bee-ff18d2051bba",
        "followers_count": 0
      },
      "slug": "nouvelle-cause-sur-la-culture-2",
      "uuid": "@uuid@",
      "author": {
        "uuid": "b4219d47-3138-5efd-9762-2ef9f9495084",
        "first_name": "Gisele",
        "last_name": "Berthoux",
        "last_name_initial": "B."
      },
      "followers_count": 1
    }
    """

  Scenario: Not approved Causes are not exposed on API
    When I send a "GET" request to "/api/causes?coalition.uuid=5b8db218-4da6-4f7f-a53e-29a7a349d45c"
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
          "name": "Cause pour la justice",
          "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
          "coalition": {
            "name": "Justice",
            "uuid": "5b8db218-4da6-4f7f-a53e-29a7a349d45c",
            "followers_count": 0
          },
          "second_coalition": null,
          "slug": "cause-pour-la-justice",
          "uuid": "44249b1d-ea10-41e0-b288-5eb74fa886ba",
          "author": {
            "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
            "first_name": "Jacques",
            "last_name": "Picard",
            "last_name_initial": "P."
          },
          "followers_count": 0,
          "image_url": null
        }
      ]
    }
    """

  Scenario: Causes of disabled coalition are not exposed on API
    When I send a "GET" request to "/api/causes?coalition.uuid=a82ee43a-c68d-4ed2-9cd5-56eb1f72d9c8"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
      "metadata": {
        "total_items": 0,
        "items_per_page": 2,
        "count": 0,
        "current_page": 1,
        "last_page": 1
      },
      "items": []
    }
    """

  Scenario: As a non logged-in user, I can filter causes by name
    When I send a "GET" request to "/api/causes?name=justice%20éducation"
    Then the response status code should be 200
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
          "name": "Cause pour l'éducation",
          "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
          "coalition": {
            "name": "Éducation",
            "uuid": "fff11d8d-5cb5-4075-b594-fea265438d65",
            "followers_count": 0
          },
          "second_coalition": null,
          "followers_count": 0,
          "slug": "cause-pour-leducation",
          "uuid": "fa6bd29c-48b7-490e-90fb-48ab5fb2ddf8",
          "author": {
            "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
            "first_name": "Jacques",
            "last_name": "Picard",
            "last_name_initial": "P."
          },
          "image_url": "http://test.enmarche.code/assets/images/causes/532c52e162feb2f6cfae99d5ed52d41f.png"
        },
        {
          "name": "Cause pour la justice",
          "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
          "coalition": {
            "name": "Justice",
            "uuid": "5b8db218-4da6-4f7f-a53e-29a7a349d45c",
            "followers_count": 0
          },
          "second_coalition": null,
          "followers_count": 0,
          "slug": "cause-pour-la-justice",
          "uuid": "44249b1d-ea10-41e0-b288-5eb74fa886ba",
          "author": {
            "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
            "first_name": "Jacques",
            "last_name": "Picard",
            "last_name_initial": "P."
          },
          "image_url": null
        }
      ]
    }
    """

    When I send a "GET" request to "/api/causes?name=pour%20la%20culture"
    Then the response status code should be 200
    And the JSON should be equal to:
    """
    {
      "metadata": {
        "total_items": 3,
        "items_per_page": 2,
        "count": 2,
        "current_page": 1,
        "last_page": 2
      },
      "items": [
        {
          "name": "Cause pour la culture",
          "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
          "coalition": {
            "name": "Culture",
            "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315",
            "followers_count": 4
          },
          "second_coalition": {
            "name": "Éducation",
            "uuid": "fff11d8d-5cb5-4075-b594-fea265438d65",
            "followers_count": 0
          },
          "followers_count": 5,
          "slug": "cause-pour-la-culture",
          "uuid": "55056e7c-2b5f-4ef6-880e-cde0511f79b2",
          "author": {
            "uuid": "e6977a4d-2646-5f6c-9c82-88e58dca8458",
            "first_name": "Carl",
            "last_name": "Mirabeau",
            "last_name_initial": "M."
          },
          "image_url": "http://test.enmarche.code/assets/images/causes/644d1c64512ab5489ab8590a3b313517.png"
        },
        {
          "name": "Cause pour la culture 2",
          "description": "Description de la cause pour la culture 2",
          "coalition": {
            "name": "Culture",
            "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315",
            "followers_count": 4
          },
          "second_coalition": {
            "name": "Éducation",
            "uuid": "fff11d8d-5cb5-4075-b594-fea265438d65",
            "followers_count": 0
          },
          "followers_count": 0,
          "slug": "cause-pour-la-culture-2",
          "uuid": "017491f9-1953-482e-b491-20418235af1f",
          "author": {
            "uuid": "e6977a4d-2646-5f6c-9c82-88e58dca8458",
            "first_name": "Carl",
            "last_name": "Mirabeau",
            "last_name_initial": "M."
          },
          "image_url": "http://test.enmarche.code/assets/images/causes/73a6283e0b639cbeb50b9b28d401eaca.png"
        }
      ]
    }
    """

  Scenario: As a logged in cause author, I should be able to edit my cause description
    Given I am logged with "carl999@example.fr" via OAuth client "Coalition App"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/causes/55056e7c-2b5f-4ef6-880e-cde0511f79b2" with body:
    """
    {
      "description": "Nouvelle description"
    }
    """
    Then the response status code should be 200
    And the JSON should be a superset of:
    """
    {
      "description": "Nouvelle description"
    }
    """

  Scenario: As a logged in cause author, i should not be able to edit my cause name
    Given I am logged with "carl999@example.fr" via OAuth client "Coalition App"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/causes/55056e7c-2b5f-4ef6-880e-cde0511f79b2" with body:
    """
    {
      "name": "Nouveau nom"
    }
    """
    Then the response status code should be 200
    And the JSON should be a superset of:
    """
    {
      "name": "Cause pour la culture"
    }
    """

  Scenario: As a logged user, i should not be allowed to edit a cause i did not authored
    Given I am logged with "carl999@example.fr" via OAuth client "Coalition App"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/causes/fa6bd29c-48b7-490e-90fb-48ab5fb2ddf8" with body:
    """
    {
      "description": "Nouvelle description"
    }
    """
    Then the response status code should be 403

  Scenario: As a non logged-in user, I can get sorted causes
    When I add "Content-Type" header equal to "application/json"
    And I send a "GET" request to "/api/causes?order[followersCount]=asc"
    Then the response status code should be 200
    And the JSON nodes should match:
      | items[0].name             | Cause pour l'éducation  |
      | items[0].followers_count  | 0                       |
      | items[1].name             | Cause pour la culture 2 |
      | items[1].followers_count  | 0                       |

    When I add "Content-Type" header equal to "application/json"
    And I send a "GET" request to "/api/causes?order[followersCount]=desc"
    Then the response status code should be 200
    And the JSON nodes should match:
      | items[0].name             | Cause pour la culture   |
      | items[1].followers_count  | 4                       |
      | items[1].name             | Cause pour l'éducation  |
      | items[1].followers_count  | 0                       |

    When I add "Content-Type" header equal to "application/json"
    And I send a "GET" request to "/api/causes?order[createdAt]=asc"
    Then the response status code should be 200
    And the JSON nodes should match:
      | items[0].name             | Cause pour la justice   |
      | items[1].name             | Cause pour l'éducation  |

    When I add "Content-Type" header equal to "application/json"
    And I send a "GET" request to "/api/causes?order[createdAt]=desc"
    Then the response status code should be 200
    And the JSON nodes should match:
      | items[0].name             | Cause pour la culture   |
      | items[1].name             | Cause pour la culture 2 |

  Scenario: As a logged-in user I can get only causes created and followed by me
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "Coalition App"
    When I send a "GET" request to "/api/v3/causes?onlyMine&page_size=10"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "metadata": {
        "total_items": 3,
        "items_per_page": 10,
        "count": 3,
        "current_page": 1,
        "last_page": 1
      },
      "items": [
        {
          "name": "Cause pour l'éducation",
          "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
          "author": {
            "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
            "first_name": "Jacques",
            "last_name": "Picard",
            "last_name_initial": "P."
          },
          "coalition": {
            "name": "Éducation",
            "uuid": "fff11d8d-5cb5-4075-b594-fea265438d65",
            "followers_count": 0
          },
          "second_coalition": null,
          "followers_count": 0,
          "slug": "cause-pour-leducation",
          "uuid": "fa6bd29c-48b7-490e-90fb-48ab5fb2ddf8",
          "image_url": "http://test.enmarche.code/assets/images/causes/532c52e162feb2f6cfae99d5ed52d41f.png"
        },
        {
          "name": "Cause pour la justice",
          "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
          "author": {
            "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
            "first_name": "Jacques",
            "last_name": "Picard",
            "last_name_initial": "P."
          },
          "coalition": {
            "name": "Justice",
            "uuid": "5b8db218-4da6-4f7f-a53e-29a7a349d45c",
            "followers_count": 0
          },
          "second_coalition": null,
          "followers_count": 0,
          "slug": "cause-pour-la-justice",
          "uuid": "44249b1d-ea10-41e0-b288-5eb74fa886ba",
          "image_url": null
        },
        {
          "name": "Cause pour la culture",
          "description": "Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
          "author": {
            "uuid": "e6977a4d-2646-5f6c-9c82-88e58dca8458",
            "first_name": "Carl",
            "last_name": "Mirabeau",
            "last_name_initial": "M."
          },
          "coalition": {
            "name": "Culture",
            "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315",
            "followers_count": 4
          },
          "second_coalition": {
            "name": "Éducation",
            "uuid": "fff11d8d-5cb5-4075-b594-fea265438d65",
            "followers_count": 0
          },
          "followers_count": 5,
          "slug": "cause-pour-la-culture",
          "uuid": "55056e7c-2b5f-4ef6-880e-cde0511f79b2",
          "image_url": "http://test.enmarche.code/assets/images/causes/644d1c64512ab5489ab8590a3b313517.png"
        }
      ]
    }
    """
