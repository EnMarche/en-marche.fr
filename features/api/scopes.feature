@api
Feature:
  In order to get scopes of an adherent
  I should be able to request them via the API

  Background:
    Given the following fixtures are loaded:
      | LoadAdherentData                |
      | LoadClientData                  |
      | LoadDistrictData                |
      | LoadReferentTagData             |
      | LoadGeoZoneData                 |
      | LoadReferentTagsZonesLinksData  |

  Scenario:
    When I am logged with "deputy@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I send a "GET" request to "/api/v3/profile/me/scopes"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    [
      {
        "code": "deputy",
        "name": "Député",
        "zones": [
          {
            "uuid": "e3f0bf9d-906e-11eb-a875-0242ac150002",
            "code": "75-1",
            "name": "Paris (1)"
          }
        ],
        "apps": [
          "data_corner"
        ]
      }
    ]
    """

  Scenario:
    When I am logged with "deputy@en-marche-dev.fr" via OAuth client "Data-Corner"
    When I send a "GET" request to "/api/v3/profile/me/scope/deputy"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "code": "deputy",
      "name": "Député",
      "zones": [
        {
          "uuid": "e3f0bf9d-906e-11eb-a875-0242ac150002",
          "code": "75-1",
          "name": "Paris (1)"
        }
      ],
      "apps": [
        "data_corner"
      ],
      "features": [
        "dashboard",
        "contacts",
        "messages",
        "mobile_app",
        "elections"
      ]
    }
    """