<?php

namespace Tests\App\Controller\EnMarche\EventManager;

use Symfony\Component\HttpFoundation\Request;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

class ReferentEventManagerControllerTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testListEvents(): void
    {
        $this->authenticateAsAdherent($this->client, 'referent@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/espace-referent/evenements');

        $this->assertCount(7, $crawler->filter('tbody tr.event__item'));
        $this->assertStringContainsString('Réunion de réflexion marseillaise', $crawler->filter('tbody tr.event__item')->eq(0)->text());
        $this->assertStringContainsString('Réunion de réflexion dammarienne', $crawler->filter('tbody tr.event__item')->eq(1)->text());
        $this->assertStringContainsString('Réunion de réflexion bellifontaine', $crawler->filter('tbody tr.event__item')->eq(2)->text());
        $this->assertStringContainsString('Event of non AL', $crawler->filter('tbody tr.event__item')->eq(3)->text());
        $this->assertStringContainsString('Événements à Fontainebleau 1', $crawler->filter('tbody tr.event__item')->eq(4)->text());
        $this->assertStringContainsString('Événements à Fontainebleau 2', $crawler->filter('tbody tr.event__item')->eq(5)->text());
        $this->assertStringContainsString('Grand Meeting de Marseille', $crawler->filter('tbody tr.event__item')->eq(6)->text());
    }
}
