@donation
Feature: The goal is to donate one time or multiple time with a subscription
  In order to donate
  As an anonymous user or connected user
  I should be able to donate punctually or subscribe foreach month

  Scenario Outline: The user have to be able to go to donation page every where
    Given the following fixtures are loaded:
      | LoadHomeBlockData |
      | LoadArticleData   |
      | LoadPageData      |
    And I am on "<url>"
    And the response status code should be 200
    Then I should see "Donner"

    When I follow "Donner"
    Then I should be on "/don"

    Examples:
      | url           |
      | /             |
      | /le-mouvement |
      | /evenements   |
      | /comites      |
      | /formation    |
      | /articles     |

  Scenario: A user can't donate more than 7500€ per year
    Given I freeze the clock to "2020-01-12"
    And the following fixtures are loaded:
      | LoadDonationData          |
      | LoadDonatorIdentifierData |
    And I am logged as "jacques.picard@en-marche.fr"
    And I am on "/don/coordonnees?montant=7490&abonnement=0"
    And I press "Finaliser mon don"
    Then I should see "Vous avez déjà donné 200 euros cette année."
    And I should see "Le don que vous vous apprêtez à faire est trop élevé, car vous avez déjà donné 200 euros cette année. Les dons étant limités à 7500 euros par an et par personne, vous pouvez encore donner 7300 euros."

  @javascript
  Scenario: An anonymous user can donate successfully
    Given the following fixtures are loaded:
      | LoadDonatorIdentifierData |
    And I am on "/don"
    And wait 2 second until I see "Une fois"
    And I press "50 €"
    When I press "Je donne maintenant"
    Then I should be on "/don/coordonnees?montant=50&abonnement=0"

    When I fill in the following:
      | Nom                      | Jean                     |
      | Prénom                   | Dupont                   |
      | app_donation_nationality | FR                       |
      | Adresse e-mail           | jean.dupont@en-marche.fr |
      | Code postal              | 75001                    |
      | Ville                    | Paris                    |
      | Adresse postale          | 1 allée vivaldie         |

    And I click the "donation_check_label" element
    And I click the "donation_check_nationality_label" element
    And I click the "field-personal-data-collection" element
    And I click the "donation_gender_male" element
    And I press "Finaliser mon don"
    Then I should be on "https://preprod-tpeweb.paybox.com/cgi/MYpagepaiement.cgi" wait otherwise
    And I should see "Numéro de carte"

    When I fill in the following:
      | NUMERO_CARTE | 4012001037141112 |
      | CVVX         | 123              |
    And I wait 2 seconds
    And I press "pbx-card-button-choice1"
    And I select "12" from "MOIS_VALIDITE"
    And I select "34" from "AN_VALIDITE"
    And I press "VALIDER"
    Then I should be on "https://preprod-tpeweb.paybox.com/cgi/MYpagepaiement.cgi" wait otherwise
    And I wait 7 second until I see "PAIEMENT ACCEPTÉ"

    When I click on the "2" "img" element
    Then I should see "Continuons à transformer notre pays ensemble !"

  @javascript
  Scenario: The user can subscribe to donate each month successfully but can't have a second subscription
    Given the following fixtures are loaded:
      | LoadDonatorIdentifierData |
    And I am on "/don"
    And wait 2 second until I see "Une fois"
    When I click the "donation-monthly_label" element
    And I press "50 €"
    And I press "Je donne maintenant"
    Then I should be on "/don/coordonnees?montant=50&abonnement=1"

    When I fill in the following:
      | Nom                      | Jean                     |
      | Prénom                   | Dupont                   |
      | app_donation_nationality | FR                       |
      | Adresse e-mail           | jean.dupont@en-marche.fr |
      | Code postal              | 75001                    |
      | Ville                    | Paris                    |
      | Adresse postale          | 1 allée vivaldie         |
    And I click the "donation_check_label" element
    And I click the "donation_check_nationality_label" element
    And I click the "field-personal-data-collection" element
    And I click the "donation_gender_male" element
    And I press "Finaliser mon don"
    Then I should be on "https://preprod-tpeweb.paybox.com/cgi/MYpagepaiement.cgi" wait otherwise
    And I should see "Numéro de carte"

    When I fill in the following:
      | NUMERO_CARTE | 4012001037141112 |
      | CVVX         | 123              |
    And I select "12" from "MOIS_VALIDITE"
    And I select "34" from "AN_VALIDITE"
    And I press "VALIDER"
    Then I should be on "https://preprod-tpeweb.paybox.com/cgi/MYpagepaiement.cgi" wait otherwise
    And I wait 7 second until I see "PAIEMENT ACCEPTÉ"

    When I click on the "2" "img" element
    And I simulate IPN call with "00000" code for the last donation of "jean.dupont@en-marche.fr"
    Then I should see "Continuons à transformer notre pays ensemble !"

    Given I am on "/don"
    And wait 2 second until I see "Une fois"
    And I click the "donation-monthly_label" element
    And I press "50 €"
    When I press "Je donne maintenant"
    Then I should be on "/don/coordonnees?montant=50&abonnement=1"

    When I fill in the following:
      | Nom                      | Jean                     |
      | Prénom                   | Dupont                   |
      | app_donation_nationality | FR                       |
      | Adresse e-mail           | jean.dupont@en-marche.fr |
      | Code postal              | 75001                    |
      | Ville                    | Paris                    |
      | Adresse postale          | 1 allée vivaldie         |

    And I click the "donation_check_label" element
    And I click the "donation_check_nationality_label" element
    And I click the "field-personal-data-collection" element
    And I click the "donation_gender_male" element
    And I press "Finaliser mon don"
    Then I should be on "/don/coordonnees?montant=50&abonnement=1"
    And I should see "Vous faites déjà un don mensuel à La République En Marche ! Vous pouvez nous contacter pour l’annuler ou faire un nouveau don unique."

    When I follow "faire un nouveau don unique"
    Then I should be on "/don/coordonnees?montant=50"
    And the "app_donation[gender]" field should contain "male"
    And the "Nom" field should contain "Jean"
    And the "Prénom" field should contain "Dupont"
    And the "Adresse e-mail" field should contain "jean.dupont@en-marche.fr"

  @javascript
  Scenario: The logged user can subscribe to donate each month successfully but can't have a second subscription without unsubscribe before
    Given the following fixtures are loaded:
      | LoadAdherentData          |
      | LoadDonatorIdentifierData |
    And I am logged as "jacques.picard@en-marche.fr"
    And I am on "/don"
    And wait 2 second until I see "Une fois"
    When I click the "donation-monthly_label" element
    And I press "50 €"
    And I press "Je donne maintenant"
    Then I should be on "/don/coordonnees?montant=50&abonnement=1"

    When I fill in the following:
      | app_donation_nationality | FR |
    And I click the "donation_check_label" element
    And I click the "donation_check_nationality_label" element
    And I click the "field-personal-data-collection" element
    And I press "Finaliser mon don"
    Then I should be on "https://preprod-tpeweb.paybox.com/cgi/MYpagepaiement.cgi" wait otherwise
    And I should see "Numéro de carte"

    When I fill in the following:
      | NUMERO_CARTE | 4012001037141112 |
      | CVVX         | 123              |
    And I select "12" from "MOIS_VALIDITE"
    And I select "34" from "AN_VALIDITE"
    And I press "VALIDER"
    Then I should be on "https://preprod-tpeweb.paybox.com/cgi/MYpagepaiement.cgi" wait otherwise
    And I wait 7 second until I see "PAIEMENT ACCEPTÉ"

    When I click on the "2" "img" element
    And I simulate IPN call with "00000" code for the last donation of "jacques.picard@en-marche.fr"
    Then I should see "Continuons à transformer notre pays ensemble !"

    # Check if I can't continue create a new subscription and then can cancel a subscription
    Given I am on "/don"
    And wait 2 second until I see "Une fois"
    And I click the "donation-monthly_label" element
    And I press "50 €"
    When I press "Je donne maintenant"
    Then I should be on "/don/coordonnees?montant=50&abonnement=1"

    When I fill in the following:
      | app_donation_nationality | FR |
    And I click the "donation_check_label" element
    And I click the "donation_check_nationality_label" element
    And I click the "field-personal-data-collection" element
    And I press "Finaliser mon don"
    Then I should be on "/don/coordonnees?montant=50&abonnement=1"
    And I should see "Vous faites déjà un don mensuel à La République En Marche ! Vous pouvez vous rendre sur votre profil pour l’annuler ou faire un nouveau don unique."

    When I follow "vous rendre sur votre profil"
    Then I should be on "/parametres/mon-compte"

    When I follow "my_donations"
    And I follow "Mettre fin à mon don mensuel"
    And I press "Oui"
    Then I should see "Votre don mensuel a bien été annulé. Vous recevrez bientôt un mail de confirmation."

    # Check if I can create a new subscription after cancel subscription
    Given I am on "/don"
    And wait 2 second until I see "Une fois"
    When I click the "donation-monthly_label" element
    And I press "50 €"
    And I press "Je donne maintenant"
    Then I should be on "/don/coordonnees?montant=50&abonnement=1"

    When I click the "donation_check_label" element
    And I click the "donation_check_nationality_label" element
    And I click the "field-personal-data-collection" element
    And I press "Finaliser mon don"
    Then I should be on "https://preprod-tpeweb.paybox.com/cgi/MYpagepaiement.cgi" wait otherwise
    And I should see "Numéro de carte"

    When I fill in the following:
      | NUMERO_CARTE | 4012001037141112 |
      | CVVX         | 123              |
    And I select "12" from "MOIS_VALIDITE"
    And I select "34" from "AN_VALIDITE"
    And I press "VALIDER"
    Then I should be on "https://preprod-tpeweb.paybox.com/cgi/MYpagepaiement.cgi" wait otherwise
    And I wait 7 second until I see "PAIEMENT ACCEPTÉ"

    When I click on the "2" "img" element
    Then I should see "Continuons à transformer notre pays ensemble !"

  @javascript
  Scenario: The logged user can continue to donate punctually with a subscription currently running
    Given the following fixtures are loaded:
      | LoadDonationData          |
      | LoadDonatorIdentifierData |
    And I am logged as "jacques.picard@en-marche.fr"
    And I am on "/don"
    And wait 2 second until I see "Une fois"
    When I click the "donation-monthly_label" element
    And I press "50 €"
    And I press "Je donne maintenant"
    Then I should be on "/don/coordonnees?montant=50&abonnement=1"

    When I fill in the following:
      | app_donation_nationality | FR |
    And I click the "donation_check_label" element
    And I click the "donation_check_nationality_label" element
    And I click the "field-personal-data-collection" element
    And I press "Finaliser mon don"
    Then I should be on "/don/coordonnees?montant=50&abonnement=1"
    And I should see "Vous faites déjà un don mensuel à La République En Marche ! Vous pouvez vous rendre sur votre profil pour l’annuler ou faire un nouveau don unique."

    When I follow "faire un nouveau don unique"
    Then I should be on "/don/coordonnees?montant=50"
    And I should not see "200€ / mois"

    When I click the "donation_check_label" element
    And I click the "donation_check_nationality_label" element
    And I click the "field-personal-data-collection" element
    And I press "Finaliser mon don"
    Then I should be on "https://preprod-tpeweb.paybox.com/cgi/MYpagepaiement.cgi" wait otherwise
    And I should see "Numéro de carte"

    When I fill in the following:
      | NUMERO_CARTE | 4012001037141112 |
      | CVVX         | 123              |
    And I wait 2 seconds
    And I press "pbx-card-button-choice1"
    And I select "12" from "MOIS_VALIDITE"
    And I select "34" from "AN_VALIDITE"
    And I press "VALIDER"
    Then I should be on "https://preprod-tpeweb.paybox.com/cgi/MYpagepaiement.cgi" wait otherwise
    And I wait 7 second until I see "PAIEMENT ACCEPTÉ"

    When I click on the "2" "img" element
    Then I should see "Continuons à transformer notre pays ensemble !"
