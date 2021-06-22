@api
Feature:
  In order to see coalition events
  As a non logged-in user
  I should be able to access API coalition events

  Background:
    Given the following fixtures are loaded:
      | LoadCauseData           |
      | LoadCoalitionData       |
      | LoadCoalitionEventData  |
      | LoadCauseEventData      |
      | LoadAdherentData        |
      | LoadUserData            |
      | LoadClientData          |

  Scenario: As a non logged-in user I see coalition events
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/coalitions/d5289058-2a35-4cf0-8f2f-a683d97d8315/events"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "metadata": {
        "total_items": 11,
        "items_per_page": 30,
        "count": 11,
        "current_page": 1,
        "last_page": 1
      },
      "items": [
        {
          "coalition": {
            "name": "Culture",
            "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315"
          },
          "uuid": "472d1f86-6522-4122-a0f4-abd69d17bb2d",
          "name": "Événement culturel 1",
          "slug": "@string@-evenement-culturel-1",
          "description": "Nous allons échanger autour de différents sujets",
          "time_zone": "Europe/Paris",
          "begin_at": "@string@.isDateTime()",
          "finish_at": "@string@.isDateTime()",
          "organizer": {
            "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
            "first_name": "Jacques",
            "last_name": "Picard"
          },
          "participants_count": 0,
          "status": "SCHEDULED",
          "capacity": null,
          "post_address": {
            "address": "60 avenue des Champs-Élysées",
            "postal_code": "75008",
            "city": "75008-75108",
            "city_name": "Paris 8e",
            "country": "FR",
            "latitude": 48.870506,
            "longitude": 2.313243
          },
          "category": {
            "event_group_category": {
              "name": "événement",
              "slug": "evenement"
            },
            "name": "Atelier du programme",
            "slug": "atelier-du-programme"
          },
          "visio_url": null,
          "mode": null,
          "image_url": null
        },
        {
          "coalition": {
            "name": "Culture",
            "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315"
          },
          "uuid": "462d7faf-09d2-4679-989e-287929f50be8",
          "name": "Événement culturel 2",
          "slug": "@string@-evenement-culturel-2",
          "description": "Nous allons échanger encore autour de différents sujets culturels",
          "time_zone": "Europe/Paris",
          "begin_at": "@string@.isDateTime()",
          "finish_at": "@string@.isDateTime()",
          "organizer": {
            "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
            "first_name": "Jacques",
            "last_name": "Picard"
          },
          "participants_count": 0,
          "status": "SCHEDULED",
          "capacity": null,
          "post_address": {
            "address": "60 avenue des Champs-Élysées",
            "postal_code": "75008",
            "city": "75008-75108",
            "city_name": "Paris 8e",
            "country": "FR",
            "latitude": 48.870506,
            "longitude": 2.313243
          },
          "category": {
            "event_group_category": {
              "name": "événement",
              "slug": "evenement"
            },
            "name": "Atelier du programme",
            "slug": "atelier-du-programme"
          },
          "visio_url": null,
          "mode": null,
          "image_url": null
        },
        {
          "coalition": {
            "name": "Culture",
            "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315"
          },
          "uuid": "d16f0ab4-292b-4698-847c-005f58ec3119",
          "name": "Événement culturel 5",
          "slug": "@string@-evenement-culturel-5",
          "description": "HAPPINESS FOR EVERYBODY, FREE, AND NO ONE WILL GO AWAY UNSATISFIED!",
          "time_zone": "Europe/Paris",
          "begin_at": "@string@.isDateTime()",
          "finish_at": "@string@.isDateTime()",
          "organizer": {
            "uuid": "46ab0600-b5a0-59fc-83a7-cc23ca459ca0",
            "first_name": "Michel",
            "last_name": "VASSEUR"
          },
          "participants_count": 0,
          "status": "SCHEDULED",
          "capacity": null,
          "post_address": {
            "address": "12 Pilgerweg",
            "postal_code": "8802",
            "city": null,
            "city_name": "Kilchberg",
            "country": "CH",
            "latitude": 47.321568,
            "longitude": 8.549969
          },
          "category": {
            "event_group_category": {
              "name": "événement",
              "slug": "evenement"
            },
            "name": "Marche",
            "slug": "marche"
          },
          "visio_url": null,
          "mode": null,
          "image_url": null
        },
        {
          "coalition": {
            "name": "Culture",
            "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315"
          },
          "uuid": "d7e72e52-b81a-4adf-b022-d547672ce095",
          "name": "Événement culturel 3",
          "slug": "@string@-evenement-culturel-3",
          "description": "Nous allons échanger encore autour de différents sujets culturels",
          "time_zone": "Europe/Paris",
          "begin_at": "@string@.isDateTime()",
          "finish_at": "@string@.isDateTime()",
          "organizer": {
            "uuid": "cd76b8cf-af20-4976-8dd9-eb067a2f30c7",
            "first_name": "Pierre",
            "last_name": "Kiroule"
          },
          "participants_count": 0,
          "status": "SCHEDULED",
          "capacity": null,
          "post_address": {
            "address": "226 W 52nd St",
            "postal_code": "10019",
            "city": null,
            "city_name": "New York",
            "country": "US",
            "latitude": 40.762527,
            "longitude": -73.985992
          },
          "category": {
            "event_group_category": {
              "name": "événement",
              "slug": "evenement"
            },
            "name": "Atelier du programme",
            "slug": "atelier-du-programme"
          },
          "visio_url": null,
          "mode": null,
          "image_url": null
        },
        {
          "coalition": {
            "name": "Culture",
            "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315"
          },
          "uuid": "aa2b835e-0944-45bb-b244-068b469c013e",
          "name": "Événement culturel 4",
          "slug": "@string@-evenement-culturel-4",
          "description": "Description d'un événement culturel",
          "time_zone": "Europe/Paris",
          "begin_at": "@string@.isDateTime()",
          "finish_at": "@string@.isDateTime()",
          "organizer": {
            "uuid": "cd76b8cf-af20-4976-8dd9-eb067a2f30c7",
            "first_name": "Pierre",
            "last_name": "Kiroule"
          },
          "participants_count": 0,
          "status": "SCHEDULED",
          "capacity": null,
          "post_address": {
            "address": "226 W 52nd St",
            "postal_code": "10019",
            "city": null,
            "city_name": "New York",
            "country": "US",
            "latitude": 40.762527,
            "longitude": -73.985992
          },
          "category": {
            "event_group_category": {
              "name": "événement",
              "slug": "evenement"
            },
            "name": "Atelier du programme",
            "slug": "atelier-du-programme"
          },
          "visio_url": null,
          "mode": null,
          "image_url": null
        },
        {
          "coalition": {
            "name": "Culture",
            "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315"
          },
          "uuid": "a9d45d86-0333-4767-9853-6e9e7268d778",
          "name": "Événement culturel 6",
          "slug": "@string@-evenement-culturel-6",
          "description": "Du bonheur pour tout le monde, gratuitement, et que personne ne reparte lésé ! ",
          "time_zone": "Europe/Paris",
          "begin_at": "@string@.isDateTime()",
          "finish_at": "@string@.isDateTime()",
          "organizer": {
            "uuid": "2f69db3c-ecd7-4a8a-bd23-bb4c9cfd70cf",
            "first_name": "Referent75and77",
            "last_name": "Referent75and77"
          },
          "participants_count": 0,
          "status": "SCHEDULED",
          "capacity": null,
          "post_address": {
            "address": "60 avenue des Champs-Élysées",
            "postal_code": "75008",
            "city": "75008-75108",
            "city_name": "Paris 8e",
            "country": "FR",
            "latitude": 48.870506,
            "longitude": 2.313243
          },
          "category": {
            "event_group_category": {
              "name": "événement",
              "slug": "evenement"
            },
            "name": "Événement innovant",
            "slug": "evenement-innovant"
          },
          "visio_url": null,
          "mode": null,
          "image_url": null
        },
        {
          "cause": {
            "name": "Cause pour la culture",
            "coalition": {
              "name": "Culture",
              "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315"
            },
            "slug": "cause-pour-la-culture",
            "uuid": "55056e7c-2b5f-4ef6-880e-cde0511f79b2"
          },
          "uuid": "ef62870c-6d42-47b6-91ea-f454d473adf8",
          "name": "Événement culturel 1 de la cause culturelle 1",
          "slug": "@string@-evenement-culturel-1-de-la-cause-culturelle-1",
          "description": "C'est un événement culturel de la cause",
          "time_zone": "Europe/Paris",
          "begin_at": "@string@.isDateTime()",
          "finish_at": "@string@.isDateTime()",
          "organizer": {
            "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
            "first_name": "Jacques",
            "last_name": "Picard"
          },
          "participants_count": 0,
          "status": "SCHEDULED",
          "capacity": null,
          "post_address": {
            "address": "60 avenue des Champs-Élysées",
            "postal_code": "75008",
            "city": "75008-75108",
            "city_name": "Paris 8e",
            "country": "FR",
            "latitude": 48.870506,
            "longitude": 2.313243
          },
          "category": {
            "event_group_category": {
              "name": "événement",
              "slug": "evenement"
            },
            "name": "Atelier du programme",
            "slug": "atelier-du-programme"
          },
          "visio_url": null,
          "mode": null,
          "image_url": null
        },
        {
          "cause": {
            "name": "Cause pour la culture",
            "coalition": {
              "name": "Culture",
              "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315"
            },
            "slug": "cause-pour-la-culture",
            "uuid": "55056e7c-2b5f-4ef6-880e-cde0511f79b2"
          },
          "uuid": "19242011-7fbe-47b7-b459-0ca724d4fca2",
          "name": "Événement culturel 2 de la cause culturelle 1",
          "slug": "@string@-evenement-culturel-2-de-la-cause-culturelle-1",
          "description": "Un autre événement culturel",
          "time_zone": "Europe/Paris",
          "begin_at": "@string@.isDateTime()",
          "finish_at": "@string@.isDateTime()",
          "organizer": {
            "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
            "first_name": "Jacques",
            "last_name": "Picard"
          },
          "participants_count": 0,
          "status": "SCHEDULED",
          "capacity": null,
          "post_address": {
            "address": "60 avenue des Champs-Élysées",
            "postal_code": "75008",
            "city": "75008-75108",
            "city_name": "Paris 8e",
            "country": "FR",
            "latitude": 48.870506,
            "longitude": 2.313243
          },
          "category": {
            "event_group_category": {
              "name": "événement",
              "slug": "evenement"
            },
            "name": "Atelier du programme",
            "slug": "atelier-du-programme"
          },
          "visio_url": null,
          "mode": null,
          "image_url": null
        },
        {
          "cause": {
            "name": "Cause pour la culture",
            "coalition": {
              "name": "Culture",
              "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315"
            },
            "slug": "cause-pour-la-culture",
            "uuid": "55056e7c-2b5f-4ef6-880e-cde0511f79b2"
          },
          "uuid": "32669ec6-dbc1-4526-92af-ad50925e23d6",
          "name": "Événement culturel 3 de la cause culturelle 1",
          "slug": "@string@-evenement-culturel-3-de-la-cause-culturelle-1",
          "description": "Nous allons échanger encore autour de différents sujets culturels",
          "time_zone": "Europe/Paris",
          "begin_at": "@string@.isDateTime()",
          "finish_at": "@string@.isDateTime()",
          "organizer": {
            "uuid": "cd76b8cf-af20-4976-8dd9-eb067a2f30c7",
            "first_name": "Pierre",
            "last_name": "Kiroule"
          },
          "participants_count": 0,
          "status": "SCHEDULED",
          "capacity": null,
          "post_address": {
            "address": "226 W 52nd St",
            "postal_code": "10019",
            "city": null,
            "city_name": "New York",
            "country": "US",
            "latitude": 40.762527,
            "longitude": -73.985992
          },
          "category": {
            "event_group_category": {
              "name": "événement",
              "slug": "evenement"
            },
            "name": "Atelier du programme",
            "slug": "atelier-du-programme"
          },
          "visio_url": null,
          "mode": null,
          "image_url": null
        },
        {
          "coalition": {
            "name": "Culture",
            "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315"
          },
          "uuid": "4e805f54-4af6-40f8-91f9-d133407289b3",
          "name": "Événement culturel passé",
          "slug": "@string@-evenement-culturel-passe",
          "description": "Cet événement est passé",
          "time_zone": "Europe/Paris",
          "begin_at": "@string@.isDateTime()",
          "finish_at": "@string@.isDateTime()",
          "organizer": {
            "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
            "first_name": "Jacques",
            "last_name": "Picard"
          },
          "participants_count": 0,
          "status": "SCHEDULED",
          "capacity": null,
          "post_address": {
            "address": "2 Place de la Major",
            "postal_code": "13002",
            "city": "13002-13202",
            "city_name": "Marseille 2e",
            "country": "FR",
            "latitude": 43.298492,
            "longitude": 5.362377
          },
          "category": {
            "event_group_category": {
              "name": "événement",
              "slug": "evenement"
            },
            "name": "Atelier du programme",
            "slug": "atelier-du-programme"
          },
          "visio_url": null,
          "mode": null,
          "image_url": null
        },
        {
          "cause": {
            "name": "Cause pour la culture",
            "coalition": {
              "name": "Culture",
              "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315"
            },
            "slug": "cause-pour-la-culture",
            "uuid": "55056e7c-2b5f-4ef6-880e-cde0511f79b2"
          },
          "uuid": "efad6d3b-52b2-4ef1-adfc-210733b1607a",
          "name": "Événement culturel passé de la cause",
          "slug": "@string@-evenement-culturel-passe-de-la-cause",
          "description": "Cet événement de la cause est passé",
          "time_zone": "Europe/Paris",
          "begin_at": "@string@.isDateTime()",
          "finish_at": "@string@.isDateTime()",
          "organizer": {
            "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
            "first_name": "Jacques",
            "last_name": "Picard"
          },
          "participants_count": 0,
          "status": "SCHEDULED",
          "capacity": null,
          "post_address": {
            "address": "2 Place de la Major",
            "postal_code": "13002",
            "city": "13002-13202",
            "city_name": "Marseille 2e",
            "country": "FR",
            "latitude": 43.298492,
            "longitude": 5.362377
          },
          "category": {
            "event_group_category": {
              "name": "événement",
              "slug": "evenement"
            },
            "name": "Atelier du programme",
            "slug": "atelier-du-programme"
          },
          "visio_url": null,
          "mode": null,
          "image_url": null
        }
      ]
    }
    """

  Scenario: As a non logged-in user I can paginate coalition events
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/coalitions/d5289058-2a35-4cf0-8f2f-a683d97d8315/events?page=2"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "metadata": {
        "total_items": 11,
        "items_per_page": 30,
        "count": 0,
        "current_page": 2,
        "last_page": 1
      },
      "items": []
    }
    """

  Scenario: As a non logged-in user I can paginate coalition events and change number events by page
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/coalitions/d5289058-2a35-4cf0-8f2f-a683d97d8315/events?page=2&page_size=5"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "metadata": {
        "total_items": 11,
        "items_per_page": 5,
        "count": 5,
        "current_page": 2,
        "last_page": 3
      },
      "items": [
        {
          "coalition": {
            "name": "Culture",
            "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315"
          },
          "uuid": "a9d45d86-0333-4767-9853-6e9e7268d778",
          "name": "Événement culturel 6",
          "slug": "@string@-evenement-culturel-6",
          "description": "Du bonheur pour tout le monde, gratuitement, et que personne ne reparte lésé ! ",
          "time_zone": "Europe/Paris",
          "begin_at": "@string@.isDateTime()",
          "finish_at": "@string@.isDateTime()",
          "organizer": {
            "uuid": "2f69db3c-ecd7-4a8a-bd23-bb4c9cfd70cf",
            "first_name": "Referent75and77",
            "last_name": "Referent75and77"
          },
          "participants_count": 0,
          "status": "SCHEDULED",
          "capacity": null,
          "post_address": {
            "address": "60 avenue des Champs-Élysées",
            "postal_code": "75008",
            "city": "75008-75108",
            "city_name": "Paris 8e",
            "country": "FR",
            "latitude": 48.870506,
            "longitude": 2.313243
          },
          "category": {
            "event_group_category": {
              "name": "événement",
              "slug": "evenement"
            },
            "name": "Événement innovant",
            "slug": "evenement-innovant"
          },
          "visio_url": null,
          "mode": null,
          "image_url": null
        },
        {
          "cause": {
            "name": "Cause pour la culture",
            "coalition": {
              "name": "Culture",
              "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315"
            },
            "slug": "cause-pour-la-culture",
            "uuid": "55056e7c-2b5f-4ef6-880e-cde0511f79b2"
          },
          "uuid": "ef62870c-6d42-47b6-91ea-f454d473adf8",
          "name": "Événement culturel 1 de la cause culturelle 1",
          "slug": "@string@-evenement-culturel-1-de-la-cause-culturelle-1",
          "description": "C'est un événement culturel de la cause",
          "time_zone": "Europe/Paris",
          "begin_at": "@string@.isDateTime()",
          "finish_at": "@string@.isDateTime()",
          "organizer": {
            "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
            "first_name": "Jacques",
            "last_name": "Picard"
          },
          "participants_count": 0,
          "status": "SCHEDULED",
          "capacity": null,
          "post_address": {
            "address": "60 avenue des Champs-Élysées",
            "postal_code": "75008",
            "city": "75008-75108",
            "city_name": "Paris 8e",
            "country": "FR",
            "latitude": 48.870506,
            "longitude": 2.313243
          },
          "category": {
            "event_group_category": {
              "name": "événement",
              "slug": "evenement"
            },
            "name": "Atelier du programme",
            "slug": "atelier-du-programme"
          },
          "visio_url": null,
          "mode": null,
          "image_url": null
        },
        {
          "cause": {
            "name": "Cause pour la culture",
            "coalition": {
              "name": "Culture",
              "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315"
            },
            "slug": "cause-pour-la-culture",
            "uuid": "55056e7c-2b5f-4ef6-880e-cde0511f79b2"
          },
          "uuid": "19242011-7fbe-47b7-b459-0ca724d4fca2",
          "name": "Événement culturel 2 de la cause culturelle 1",
          "slug": "@string@-evenement-culturel-2-de-la-cause-culturelle-1",
          "description": "Un autre événement culturel",
          "time_zone": "Europe/Paris",
          "begin_at": "@string@.isDateTime()",
          "finish_at": "@string@.isDateTime()",
          "organizer": {
            "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
            "first_name": "Jacques",
            "last_name": "Picard"
          },
          "participants_count": 0,
          "status": "SCHEDULED",
          "capacity": null,
          "post_address": {
            "address": "60 avenue des Champs-Élysées",
            "postal_code": "75008",
            "city": "75008-75108",
            "city_name": "Paris 8e",
            "country": "FR",
            "latitude": 48.870506,
            "longitude": 2.313243
          },
          "category": {
            "event_group_category": {
              "name": "événement",
              "slug": "evenement"
            },
            "name": "Atelier du programme",
            "slug": "atelier-du-programme"
          },
          "visio_url": null,
          "mode": null,
          "image_url": null
        },
        {
          "cause": {
            "name": "Cause pour la culture",
            "coalition": {
              "name": "Culture",
              "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315"
            },
            "slug": "cause-pour-la-culture",
            "uuid": "55056e7c-2b5f-4ef6-880e-cde0511f79b2"
          },
          "uuid": "32669ec6-dbc1-4526-92af-ad50925e23d6",
          "name": "Événement culturel 3 de la cause culturelle 1",
          "slug": "@string@-evenement-culturel-3-de-la-cause-culturelle-1",
          "description": "Nous allons échanger encore autour de différents sujets culturels",
          "time_zone": "Europe/Paris",
          "begin_at": "@string@.isDateTime()",
          "finish_at": "@string@.isDateTime()",
          "organizer": {
            "uuid": "cd76b8cf-af20-4976-8dd9-eb067a2f30c7",
            "first_name": "Pierre",
            "last_name": "Kiroule"
          },
          "participants_count": 0,
          "status": "SCHEDULED",
          "capacity": null,
          "post_address": {
            "address": "226 W 52nd St",
            "postal_code": "10019",
            "city": null,
            "city_name": "New York",
            "country": "US",
            "latitude": 40.762527,
            "longitude": -73.985992
          },
          "category": {
            "event_group_category": {
              "name": "événement",
              "slug": "evenement"
            },
            "name": "Atelier du programme",
            "slug": "atelier-du-programme"
          },
          "visio_url": null,
          "mode": null,
          "image_url": null
        },
        {
          "coalition": {
            "name": "Culture",
            "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315"
          },
          "uuid": "4e805f54-4af6-40f8-91f9-d133407289b3",
          "name": "Événement culturel passé",
          "slug": "@string@-evenement-culturel-passe",
          "description": "Cet événement est passé",
          "time_zone": "Europe/Paris",
          "begin_at": "@string@.isDateTime()",
          "finish_at": "@string@.isDateTime()",
          "organizer": {
            "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
            "first_name": "Jacques",
            "last_name": "Picard"
          },
          "participants_count": 0,
          "status": "SCHEDULED",
          "capacity": null,
          "post_address": {
            "address": "2 Place de la Major",
            "postal_code": "13002",
            "city": "13002-13202",
            "city_name": "Marseille 2e",
            "country": "FR",
            "latitude": 43.298492,
            "longitude": 5.362377
          },
          "category": {
            "event_group_category": {
              "name": "événement",
              "slug": "evenement"
            },
            "name": "Atelier du programme",
            "slug": "atelier-du-programme"
          },
          "visio_url": null,
          "mode": null,
          "image_url": null
        }
      ]
    }
    """

  Scenario: As a logged-in adherent I can not create a coalition event with invalid data
    Given I am logged with "gisele-berthoux@caramail.com" via OAuth client "Coalition App" with scope "write:event"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/events" with body:
    """
    {
       "type":"coalition"
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "type": "https://tools.ietf.org/html/rfc2616#section-10",
      "title": "An error occurred",
      "detail": "coalition: Cette valeur ne doit pas être vide.\nname: Cette valeur ne doit pas être vide.\ncanonical_name: Cette valeur ne doit pas être vide.\ndescription: Cette valeur ne doit pas être vide.\nbegin_at: Cette valeur ne doit pas être vide.\nfinish_at: Cette valeur ne doit pas être vide.",
      "violations": [
        {
          "code": "@uuid@",
          "propertyPath": "coalition",
          "message": "Cette valeur ne doit pas être vide."
        },
        {
          "code": "@uuid@",
          "propertyPath": "name",
          "message": "Cette valeur ne doit pas être vide."
        },
        {
          "code": "@uuid@",
          "propertyPath": "canonical_name",
          "message": "Cette valeur ne doit pas être vide."
        },
        {
          "code": "@uuid@",
          "propertyPath": "description",
          "message": "Cette valeur ne doit pas être vide."
        },
        {
          "code": "@uuid@",
          "propertyPath": "begin_at",
          "message": "Cette valeur ne doit pas être vide."
        },
        {
          "code": "@uuid@",
          "propertyPath": "finish_at",
          "message": "Cette valeur ne doit pas être vide."
        }
      ]
    }
    """

  Scenario: As a logged-in adherent I can create a coalition event
    Given I am logged with "gisele-berthoux@caramail.com" via OAuth client "Coalition App" with scopes "write:event"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/events" with body:
    """
    {
       "type": "coalition",
       "name": "My coalition event",
       "description": "Description",
       "time_zone": "Europe/Paris",
       "begin_at": "2022-04-29 16:30:30",
       "finish_at": "2022-04-30 16:30:30",
       "category": "kiosque",
       "mode": "online",
       "visio_url": "visio.fr",
       "image_url": null,
       "post_address": {
          "address": "50 rue de la villette",
          "postal_code": "69003",
          "city_name": "Lyon 3e",
          "country": "FR"
      },
       "coalition": "fc7fd104-71e5-4399-a874-f8fe752f846b"
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "coalition": {
        "name": "Économie",
        "uuid": "fc7fd104-71e5-4399-a874-f8fe752f846b"
      },
      "category": {
        "event_group_category": {
          "name": "événement",
          "slug": "evenement"
        },
        "name": "Kiosque",
        "slug": "kiosque"
      },
      "uuid": "@string@",
      "name": "My coalition event",
      "slug": "@string@-my-coalition-event",
      "description": "Description",
      "time_zone": "Europe/Paris",
      "begin_at": "2022-04-29T16:30:30+02:00",
      "finish_at": "2022-04-30T16:30:30+02:00",
      "organizer": {
        "uuid": "b4219d47-3138-5efd-9762-2ef9f9495084",
        "first_name": "Gisele",
        "last_name": "Berthoux"
      },
      "participants_count": 1,
      "status": "SCHEDULED",
      "capacity": null,
      "post_address": {
        "address": "50 rue de la villette",
        "postal_code": "69003",
        "city": "69003-69383",
        "city_name": "Lyon 3e",
        "country": "FR",
        "latitude": 45.7596356,
        "longitude": 4.8614359
      },
      "visio_url": "https://visio.fr",
      "mode": "online",
      "image_url": null
    }
    """
    And I should have 0 email

  Scenario: As a logged-in user I can create a coalition event
    Given I am logged with "simple-user@example.ch" via OAuth client "Coalition App" with scopes "write:event"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/events" with body:
    """
    {
       "type": "coalition",
       "name": "My coalition event",
       "description": "Description",
       "time_zone": "Europe/Paris",
       "begin_at": "2022-04-29 16:30:30",
       "finish_at": "2022-04-30 16:30:30",
       "category": "kiosque",
       "mode": "online",
       "visio_url": "http://visio.fr",
       "image_url": null,
       "post_address": {
          "address": "50 rue de la villette",
          "postal_code": "69003",
          "city_name": "Lyon 3e",
          "country": "FR"
      },
       "coalition": "fc7fd104-71e5-4399-a874-f8fe752f846b"
    }
    """
    Then the response status code should be 201
    And the response should be in JSON

  Scenario: As a logged-in user I can edit a coalition event
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "Coalition App" with scopes "write:event"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/events/472d1f86-6522-4122-a0f4-abd69d17bb2d" with body:
    """
    {
      "name": "Nouvel objectif",
      "description": "Nouvelle description",
      "begin_at": "2022-05-15 16:30:30",
      "finish_at": "2022-05-16 16:30:30",
      "category": "reunion-dequipe",
      "mode": "online",
      "visio_url": "http://visio.fr",
      "image_url": null,
      "post_address": {
        "address": "50 rue de la villette",
        "postal_code": "69003",
        "city_name": "Lyon 3e",
        "country": "FR"
      }
    }
    """
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "coalition": {
        "name": "Culture",
        "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315"
      },
      "category": {
        "event_group_category": {
          "name": "événement",
          "slug": "evenement"
        },
        "name": "Réunion d'équipe",
        "slug": "reunion-dequipe"
      },
      "uuid": "472d1f86-6522-4122-a0f4-abd69d17bb2d",
      "name": "Nouvel objectif",
      "slug": "2022-05-15-nouvel-objectif",
      "description": "Nouvelle description",
      "time_zone": "Europe/Paris",
      "begin_at": "2022-05-15T16:30:30+02:00",
      "finish_at": "2022-05-16T16:30:30+02:00",
      "organizer": {
        "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
        "first_name": "Jacques",
        "last_name": "Picard"
      },
      "participants_count": 0,
      "status": "SCHEDULED",
      "capacity": null,
      "post_address": {
        "address": "50 rue de la villette",
        "postal_code": "69003",
        "city": "69003-69383",
        "city_name": "Lyon 3e",
        "country": "FR",
        "latitude": 45.7596356,
        "longitude": 4.8614359
      },
      "visio_url": "http://visio.fr",
      "mode": "online",
      "image_url": null
    }
    """

  Scenario: As a logged-in user I can not create a cause event with invalid data
    Given I am logged with "gisele-berthoux@caramail.com" via OAuth client "Coalition App" with scope "write:event"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/events" with body:
    """
    {
       "type":"cause"
    }
    """
    Then the response status code should be 400
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "type": "https://tools.ietf.org/html/rfc2616#section-10",
      "title": "An error occurred",
      "detail": "cause: Cette valeur ne doit pas être vide.\nname: Cette valeur ne doit pas être vide.\ncanonical_name: Cette valeur ne doit pas être vide.\ndescription: Cette valeur ne doit pas être vide.\nbegin_at: Cette valeur ne doit pas être vide.\nfinish_at: Cette valeur ne doit pas être vide.",
      "violations": [
        {
          "code": "@uuid@",
          "propertyPath": "cause",
          "message": "Cette valeur ne doit pas être vide."
        },
        {
          "code": "@uuid@",
          "propertyPath": "name",
          "message": "Cette valeur ne doit pas être vide."
        },
        {
          "code": "@uuid@",
          "propertyPath": "canonical_name",
          "message": "Cette valeur ne doit pas être vide."
        },
        {
          "code": "@uuid@",
          "propertyPath": "description",
          "message": "Cette valeur ne doit pas être vide."
        },
        {
          "code": "@uuid@",
          "propertyPath": "begin_at",
          "message": "Cette valeur ne doit pas être vide."
        },
        {
          "code": "@uuid@",
          "propertyPath": "finish_at",
          "message": "Cette valeur ne doit pas être vide."
        }
      ]
    }
    """

  Scenario: As a logged-in adherent I can create a cause event
    Given I am logged with "gisele-berthoux@caramail.com" via OAuth client "Coalition App" with scope "write:event"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/events" with body:
    """
    {
       "type": "cause",
       "name": "My cause event",
       "description": "Description",
       "time_zone": "Europe/Paris",
       "begin_at": "2022-05-29 10:30:30",
       "finish_at": "2022-05-29 16:30:30",
       "category": "kiosque",
       "mode": "online",
       "visio_url": "visio.fr",
       "post_address": {
          "address": "50 rue de la villette",
          "postal_code": "69003",
          "city_name": "Lyon 3e",
          "country": "FR"
      },
       "cause": "55056e7c-2b5f-4ef6-880e-cde0511f79b2"
    }
    """
    Then the response status code should be 201
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "cause": {
        "name": "Cause pour la culture",
        "coalition": {
          "name": "Culture",
          "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315"
        },
        "slug": "cause-pour-la-culture",
        "uuid": "55056e7c-2b5f-4ef6-880e-cde0511f79b2"
      },
      "category": {
        "event_group_category": {
          "name": "événement",
          "slug": "evenement"
        },
        "name": "Kiosque",
        "slug": "kiosque"
      },
      "uuid": "@string@",
      "name": "My cause event",
      "slug": "@string@-my-cause-event",
      "description": "Description",
      "time_zone": "Europe/Paris",
      "begin_at": "2022-05-29T10:30:30+02:00",
      "finish_at": "2022-05-29T16:30:30+02:00",
      "organizer": {
        "uuid": "b4219d47-3138-5efd-9762-2ef9f9495084",
        "first_name": "Gisele",
        "last_name": "Berthoux"
      },
      "participants_count": 1,
      "status": "SCHEDULED",
      "capacity": null,
      "post_address": {
        "address": "50 rue de la villette",
        "postal_code": "69003",
        "city": "69003-69383",
        "city_name": "Lyon 3e",
        "country": "FR",
        "latitude": 45.7596356,
        "longitude": 4.8614359
      },
      "visio_url": "https://visio.fr",
      "mode": "online",
      "image_url": null
    }
    """
    And I should have 1 email
    And I should have 1 email "CauseEventCreationMessage" for "jacques.picard@en-marche.fr" with payload:
    """
    {
      "template_name": "cause-event-creation",
      "template_content": [],
      "message": {
        "subject": "✊ Nouvel événement sur une cause que vous soutenez",
        "from_email": "contact@pourunecause.fr",
        "global_merge_vars": [
          {
            "name": "cause_name",
            "content": "Cause pour la culture"
          },
          {
            "name": "cause_link",
            "content": "http://coalitions.code/cause/55056e7c-2b5f-4ef6-880e-cde0511f79b2"
          },
          {
            "name": "event_name",
            "content": "My cause event"
          },
          {
            "name": "event_description",
            "content": "Description"
          },
          {
            "name": "event_link",
            "content": "http://coalitions.code/cause/cause-pour-la-culture?eventId=@uuid@"
          },
          {
            "name": "event_date",
            "content": "dimanche 29 mai 2022"
          },
          {
            "name": "event_hour",
            "content": "10h30"
          },
          {
            "name": "event_address",
            "content": "50 rue de la villette, 69003 Lyon 3e"
          },
          {
            "name": "event_online",
            "content": true
          },
          {
            "name": "event_visio_url",
            "content": "https://visio.fr"
          }
        ],
        "merge_vars": [
          {
            "rcpt": "jacques.picard@en-marche.fr",
            "vars": [
              {
                "name": "first_name",
                "content": "Jacques"
              }
            ]
          },
          {
            "rcpt": "carl999@example.fr",
            "vars": [
              {
                "name": "first_name",
                "content": "Carl"
              }
            ]
          },
          {
            "rcpt": "referent@en-marche-dev.fr",
            "vars": [
              {
                "name": "first_name",
                "content": "Referent"
              }
            ]
          },
          {
            "rcpt": "adherent@en-marche-dev.fr",
            "vars": [
              {
                "name": "first_name",
                "content": "Follower"
              }
            ]
          },
          {
            "rcpt": "jean-paul@dupont.tld",
            "vars": [
              {
                "name": "first_name",
                "content": "Jean"
              }
            ]
          }
        ],
        "from_name": "Pour une cause",
        "to": [
          {
            "email": "jacques.picard@en-marche.fr",
            "type": "to",
            "name": "Jacques"
          },
          {
            "email": "carl999@example.fr",
            "type": "to",
            "name": "Carl"
          },
          {
            "email": "referent@en-marche-dev.fr",
            "type": "to",
            "name": "Referent"
          },
          {
            "email": "adherent@en-marche-dev.fr",
            "type": "to",
            "name": "Follower"
          },
          {
            "email": "jean-paul@dupont.tld",
            "type": "to",
            "name": "Jean"
          }
        ]
      }
    }
    """

  Scenario: As a logged-in user I can create a cause event
    Given I am logged with "simple-user@example.ch" via OAuth client "Coalition App" with scope "write:event"
    When I add "Content-Type" header equal to "application/json"
    And I send a "POST" request to "/api/v3/events" with body:
    """
    {
       "type": "cause",
       "name": "My cause event",
       "description": "Description",
       "time_zone": "Europe/Paris",
       "begin_at": "2022-05-29 10:30:30",
       "finish_at": "2022-05-29 16:30:30",
       "category": "kiosque",
       "mode": "online",
       "visio_url": "http://visio.fr",
       "post_address": {
          "address": "50 rue de la villette",
          "postal_code": "69003",
          "city_name": "Lyon 3e",
          "country": "FR"
      },
       "cause": "55056e7c-2b5f-4ef6-880e-cde0511f79b2"
    }
    """
    Then the response status code should be 201
    And the response should be in JSON

  Scenario: As a logged-in user I can edit a cause event
    Given I am logged with "jacques.picard@en-marche.fr" via OAuth client "Coalition App" with scope "write:event"
    When I add "Content-Type" header equal to "application/json"
    And I send a "PUT" request to "/api/v3/events/ef62870c-6d42-47b6-91ea-f454d473adf8" with body:
    """
    {
      "name": "Nouvel objectif",
      "description": "Nouvelle description",
      "time_zone": "Europe/Paris",
      "begin_at": "2022-05-12 10:30:30",
      "finish_at": "2022-05-12 16:30:30",
      "category": "kiosque",
      "mode": "online",
      "visio_url": "http://visio.fr",
      "post_address": {
        "address": "50 rue de la villette",
        "postal_code": "69003",
        "city_name": "Lyon 3e",
        "country": "FR"
      }
    }
    """
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "cause": {
        "name": "Cause pour la culture",
        "coalition": {
          "name": "Culture",
          "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315"
        },
        "slug": "cause-pour-la-culture",
        "uuid": "55056e7c-2b5f-4ef6-880e-cde0511f79b2"
      },
      "category": {
        "event_group_category": {
          "name": "événement",
          "slug": "evenement"
        },
        "name": "Kiosque",
        "slug": "kiosque"
      },
      "uuid": "ef62870c-6d42-47b6-91ea-f454d473adf8",
      "name": "Nouvel objectif",
      "slug": "2022-05-12-nouvel-objectif",
      "description": "Nouvelle description",
      "time_zone": "Europe/Paris",
      "begin_at": "2022-05-12T10:30:30+02:00",
      "finish_at": "2022-05-12T16:30:30+02:00",
      "organizer": {
        "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
        "first_name": "Jacques",
        "last_name": "Picard"
      },
      "participants_count": 0,
      "status": "SCHEDULED",
      "capacity": null,
      "post_address": {
        "address": "50 rue de la villette",
        "postal_code": "69003",
        "city": "69003-69383",
        "city_name": "Lyon 3e",
        "country": "FR",
        "latitude": 45.7596356,
        "longitude": 4.8614359
      },
      "visio_url": "http://visio.fr",
      "mode": "online",
      "image_url": null
    }
    """
    And I should have 1 email
    And I should have 1 email "CoalitionsEventUpdateMessage" for "jacques.picard@en-marche.fr" with payload:
    """
    {
      "template_name": "coalitions-event-update",
      "template_content": [],
      "message": {
        "subject": "✊ Un événement a été modifié",
        "from_email": "contact@pourunecause.fr",
        "global_merge_vars": [
          {
            "name": "event_name",
            "content": "Nouvel objectif"
          },
          {
            "name": "event_url",
            "content": "http://coalitions.code/cause/cause-pour-la-culture?eventId=ef62870c-6d42-47b6-91ea-f454d473adf8"
          },
          {
            "name": "event_date",
            "content": "jeudi 12 mai 2022"
          },
          {
            "name": "event_hour",
            "content": "10h30"
          },
          {
            "name": "event_address",
            "content": "50 rue de la villette, 69003 Lyon 3e"
          },
          {
            "name": "event_online",
            "content": true
          },
          {
            "name": "event_visio_url",
            "content": "http://visio.fr"
          }
        ],
        "merge_vars": [
          {
            "rcpt": "jacques.picard@en-marche.fr",
            "vars": [
              {
                "name": "first_name",
                "content": "Jacques"
              }
            ]
          }
        ],
        "from_name": "Pour une cause",
        "to": [
          {
            "email": "jacques.picard@en-marche.fr",
            "type": "to",
            "name": "Jacques"
          }
        ]
      }
    }
    """

  Scenario: As a non logged-in user I see cause events
    Given I add "Accept" header equal to "application/json"
    When I send a "GET" request to "/api/causes/55056e7c-2b5f-4ef6-880e-cde0511f79b2/events"
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
          "cause": {
            "name": "Cause pour la culture",
            "coalition": {
              "name": "Culture",
              "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315"
            },
            "slug": "cause-pour-la-culture",
            "uuid": "55056e7c-2b5f-4ef6-880e-cde0511f79b2"
          },
          "category": {
            "event_group_category": {
              "name": "événement",
              "slug": "evenement"
            },
            "name": "Atelier du programme",
            "slug": "atelier-du-programme"
          },
          "uuid": "ef62870c-6d42-47b6-91ea-f454d473adf8",
          "name": "Événement culturel 1 de la cause culturelle 1",
          "slug": "@string@-evenement-culturel-1-de-la-cause-culturelle-1",
          "description": "C'est un événement culturel de la cause",
          "time_zone": "Europe/Paris",
          "begin_at": "@string@.isDateTime()",
          "finish_at": "@string@.isDateTime()",
          "organizer": {
            "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
            "first_name": "Jacques",
            "last_name": "Picard"
          },
          "participants_count": 0,
          "status": "SCHEDULED",
          "capacity": null,
          "post_address": {
            "address": "60 avenue des Champs-Élysées",
            "postal_code": "75008",
            "city": "75008-75108",
            "city_name": "Paris 8e",
            "country": "FR",
            "latitude": 48.870506,
            "longitude": 2.313243
          },
          "visio_url": null,
          "image_url": null,
          "mode": null
        },
        {
          "cause": {
            "name": "Cause pour la culture",
            "coalition": {
              "name": "Culture",
              "uuid": "d5289058-2a35-4cf0-8f2f-a683d97d8315"
            },
            "slug": "cause-pour-la-culture",
            "uuid": "55056e7c-2b5f-4ef6-880e-cde0511f79b2"
          },
          "category": {
            "event_group_category": {
              "name": "événement",
              "slug": "evenement"
            },
            "name": "Atelier du programme",
            "slug": "atelier-du-programme"
          },
          "uuid": "19242011-7fbe-47b7-b459-0ca724d4fca2",
          "name": "Événement culturel 2 de la cause culturelle 1",
          "slug": "@string@-evenement-culturel-2-de-la-cause-culturelle-1",
          "description": "Un autre événement culturel",
          "time_zone": "Europe/Paris",
          "begin_at": "@string@.isDateTime()",
          "finish_at": "@string@.isDateTime()",
          "organizer": {
            "uuid": "a046adbe-9c7b-56a9-a676-6151a6785dda",
            "first_name": "Jacques",
            "last_name": "Picard"
          },
          "participants_count": 0,
          "status": "SCHEDULED",
          "capacity": null,
          "post_address": {
            "address": "60 avenue des Champs-Élysées",
            "postal_code": "75008",
            "city": "75008-75108",
            "city_name": "Paris 8e",
            "country": "FR",
            "latitude": 48.870506,
            "longitude": 2.313243
          },
          "visio_url": null,
          "image_url": null,
          "mode": null
        }
      ]
    }
    """
