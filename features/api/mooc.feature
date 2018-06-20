Feature:
  In order to get a Mooc configuration
  As a non logged-in user
  I should be able to access API Mooc

  Background:
    Given the following fixtures are loaded:
      | LoadMoocData        |
      | LoadMoocChapterData |
      | LoadMoocVideoData   |
      | LoadMoocQuizData    |

  Scenario: As a non logged-in user I can get MOOC configuration
    When I am on "/api/mooc/faire-de-sa-fourchette-un-acte-politique"
    Then the response status code should be 200
    And the response should be in JSON
    And the JSON should be equal to:
    """
    {
      "title":"Faire de sa fourchette un acte politique",
      "slug":"faire-de-sa-fourchette-un-acte-politique",
      "content": "<strong>Lorem ipsum</strong> dolor sit amet, consectetur adipiscing elit.",
      "youtubeId": "ktHEfEDhscU",
      "youtubeThumbnail": "https://img.youtube.com/vi/ktHEfEDhscU/0.jpg",
      "youtubeDuration": "00:02:10",
      "shareTwitterText": "Bonsoir, voici un tweet de partage d'un MOOC #enmarche",
      "shareFacebookText": "Bonsoir, voici un partage avec Facebook",
      "shareEmailSubject": "Bonsoir, voici un email de partage !",
      "shareEmailBody": "Voici le contenu de l'email de partage. Merci.",
      "elements":[
        {
          "type":"chapter",
          "title":"Semaine 1 : Le coup de fourchette pour détendre notre santé",
          "slug":"semaine-1-le-coup-de-fourchette-pour-detendre-notre-sante",
          "publishedAt":"@string@.isDateTime()"
        },
        {
          "type":"video",
          "title":"Les produits transformés dans une première vidéo",
          "slug":"les-produits-transformes-dans-une-premiere-video",
          "content":"Lorem ipsum dolor sit amet, consectetur adipiscing elit.",
          "shareTwitterText": "Bonsoir, voici un tweet de partage d'un MOOC #enmarche",
          "shareFacebookText": "Bonsoir, voici un partage avec Facebook",
          "shareEmailSubject": "Bonsoir, voici un email de partage !",
          "shareEmailBody": "Voici le contenu de l'email de partage. Merci.",
          "links":[
            {
              "linkName":"Site officiel de La République En Marche",
              "linkUrl":"http://www.en-marche.fr"
            },
            {
              "linkName":"Les sites départementaux de La République En Marche",
              "linkUrl":"http://dpt.en-marche.fr"
            }
          ],
          "attachments":[],
          "youtubeId":"ktHEfEDhscU",
          "youtubeThumbnail":"https://img.youtube.com/vi/ktHEfEDhscU/0.jpg",
          "duration":"00:02:10"
        },
        {
          "type":"video",
          "title":"Les produits transformés dans une deuxième vidéo",
          "slug":"les-produits-transformes-dans-une-deuxieme-video",
          "content":"Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et.",
          "shareTwitterText": "Bonsoir, voici un tweet de partage d'un MOOC #enmarche",
          "shareFacebookText": "Bonsoir, voici un partage avec Facebook",
          "shareEmailSubject": "Bonsoir, voici un email de partage !",
          "shareEmailBody": "Voici le contenu de l'email de partage. Merci.",
          "links":[],
          "attachments":[],
          "youtubeId":"ktHEfEDhscU",
          "youtubeThumbnail":"https://img.youtube.com/vi/ktHEfEDhscU/0.jpg",
          "duration":"01:30:00"
        },
        {
          "type":"quiz",
          "title":"Le test de votre vie",
          "slug":"le-test-de-votre-vie",
          "content":"<p>une description</p>",
          "shareTwitterText": "Bonsoir, voici un tweet de partage d'un MOOC #enmarche",
          "shareFacebookText": "Bonsoir, voici un partage avec Facebook",
          "shareEmailSubject": "Bonsoir, voici un email de partage !",
          "shareEmailBody": "Voici le contenu de l'email de partage. Merci.",
          "links":[],
          "attachments":[],
          "typeformUrl":"https://developerplatform.typeform.com/to/Xc7NMh"
        },
        {
          "type":"chapter",
          "title":"Semaine 2 : Le coup de fourchette pour défendre la nature",
          "slug":"semaine-2-le-coup-de-fourchette-pour-defendre-la-nature",
          "publishedAt":"@string@.isDateTime()"
        }
      ]
    }
    """
