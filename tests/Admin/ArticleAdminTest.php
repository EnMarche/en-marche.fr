<?php

namespace Tests\App\Admin;

use App\Entity\Article;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\App\AbstractWebCaseTest as WebTestCase;
use Tests\App\Controller\ControllerTestTrait;

/**
 * @group functional
 * @group admin
 */
class ArticleAdminTest extends WebTestCase
{
    use ControllerTestTrait;

    public function testCreateCategoryFail(): void
    {
        $this->authenticateAsAdmin($this->client, 'superadmin@en-marche-dev.fr');

        $crawler = $this->client->request(Request::METHOD_GET, '/admin/app/article/create');
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $form = $crawler->selectButton('btn_create_and_edit')->form();
        $this->client->submit($form);

        $this->assertStatusCode(Response::HTTP_OK, $this->client);
//        $this->assertValidationErrors(
//            [
//                'data.title',
//                'data.description',
//                'data.content',
//                'data.slug',
//                'data.media',
//            ],
//            $this->client->getContainer()
//        );
    }

    public function testEditSlugToTriggerRedirectionListener(): void
    {
        $this->authenticateAsAdmin($this->client, 'superadmin@en-marche-dev.fr');

        /** @var Article $article */
        $article = $this->manager->getRepository(Article::class)->findOneBySlug('outre-mer');

        $this->client->request(
            Request::METHOD_GET,
            sprintf('/articles/%s/%s', $article->getCategory()->getSlug(), $article->getSlug())
        );

        $this->assertStatusCode(Response::HTTP_OK, $this->client);

        $crawler = $this->client->request(
            Request::METHOD_GET,
            sprintf('/admin/app/article/%s/edit', $article->getId())
        );
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $form = $crawler->selectButton('btn_update_and_edit')->form();

        $prefix = $this->getFirstPrefixForm($form);
        $form->setValues([
            $prefix.'[slug]' => 'outre-mer-new',
            $prefix.'[description]' => 'Vous devez saisir au moins 10 caractères.',
        ]);
        $this->client->submit($form);

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);

        $this->client->request(
            Request::METHOD_GET,
            sprintf('/articles/%s/%s', $article->getCategory()->getSlug(), $article->getSlug())
        );

        $this->assertClientIsRedirectedTo('/articles/actualites/outre-mer-new', $this->client, false, true);
    }

    public function testEditWithoutRedirection()
    {
        $this->authenticateAsAdmin($this->client, 'superadmin@en-marche-dev.fr');

        /** @var Article $article */
        $article = $this->manager->getRepository(Article::class)->findOneBySlug('outre-mer');

        $crawler = $this->client->request(
            Request::METHOD_GET,
            sprintf('/admin/app/article/%s/edit', $article->getId())
        );
        $this->assertStatusCode(Response::HTTP_OK, $this->client);
        $form = $crawler->selectButton('btn_update_and_edit')->form();

        $prefix = $this->getFirstPrefixForm($form);
        $form->setValues([
            $prefix.'[description]' => 'Vous devez saisir au moins 10 caractères.',
        ]);
        $this->client->submit($form);

        $this->assertStatusCode(Response::HTTP_FOUND, $this->client);
    }
}
