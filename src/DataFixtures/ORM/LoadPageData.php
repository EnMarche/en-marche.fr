<?php

namespace App\DataFixtures\ORM;

use App\Content\MediaFactory;
use App\Content\PageFactory;
use App\Entity\Page;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\HttpFoundation\File\File;

class LoadPageData extends Fixture
{
    private $pageFactory;
    private $mediaFactory;
    private $storage;

    public function __construct(PageFactory $pageFactory, MediaFactory $mediaFactory, FilesystemInterface $storage)
    {
        $this->pageFactory = $pageFactory;
        $this->mediaFactory = $mediaFactory;
        $this->storage = $storage;
    }

    public function load(ObjectManager $manager)
    {
        $description = 'Pour ceux qui sont convaincus que le pays est bloqué, qui ont le goût du travail, du progrès, '.
            'du risque, qui vivent pour la liberté, l\'égalité, et l\'Europe.';

        $mediaFile = new File(__DIR__.'/../../../app/data/dist/10decembre.jpg');
        $this->storage->put('images/page.jpg', file_get_contents($mediaFile->getPathname()));
        $media = $this->mediaFactory->createFromFile('Page image', 'page.jpg', $mediaFile);

        $manager->persist($media);
        $manager->flush();

        $manager->persist($this->pageFactory->createFromArray([
            'keywords' => 'emmanuel macron',
            'title' => 'Emmanuel Macron - Ce que je suis',
            'slug' => 'emmanuel-macron',
            'description' => $description,
            'content' => trim(self::$ceQueJeSuis),
            'media' => $media,
        ]));

        $manager->persist($this->pageFactory->createFromArray([
            'keywords' => 'emmanuel macron révolution',
            'title' => 'Emmanuel Macron - Révolution',
            'slug' => 'emmanuel-macron/revolution',
            'description' => $description,
            'content' => file_get_contents(__DIR__.'/../content.md'),
            'media' => $media,
        ]));

        $manager->persist($this->pageFactory->createFromArray([
            'keywords' => 'mouvement en marche',
            'title' => 'Le mouvement - Nos valeurs',
            'slug' => 'le-mouvement',
            'description' => $description,
            'content' => trim(self::$nosValeurs),
            'media' => $media,
        ]));

        $manager->persist($this->pageFactory->createFromArray([
            'keywords' => 'mouvement en marche organisation porte-parole',
            'title' => 'Le mouvement - Notre organisation',
            'slug' => 'le-mouvement/notre-organisation',
            'description' => $description,
            'content' => file_get_contents(__DIR__.'/../organization.html'),
            'media' => $media,
        ]));

        $manager->persist($this->pageFactory->createFromArray([
            'keywords' => 'mouvement en marche organigramme',
            'title' => 'Le mouvement - Organigramme',
            'slug' => 'le-mouvement/organigramme',
            'description' => $description,
            'content' => file_get_contents(__DIR__.'/../content.md'),
            'media' => $media,
        ]));

        $manager->persist($this->pageFactory->createFromArray([
            'keywords' => 'mouvement en marche organisation comité comités',
            'title' => 'Le mouvement - Les comités',
            'slug' => 'le-mouvement/les-comites',
            'description' => $description,
            'content' => file_get_contents(__DIR__.'/../content.md'),
            'media' => $media,
        ]));

        $manager->persist($this->pageFactory->createFromArray([
            'keywords' => 'mouvement en marche bénévole volontaire',
            'title' => 'Le mouvement - Devenez bénévole',
            'slug' => 'le-mouvement/devenez-benevole',
            'description' => $description,
            'content' => file_get_contents(__DIR__.'/../content.md'),
            'media' => $media,
        ]));

        $manager->persist($this->pageFactory->createFromArray([
            'keywords' => 'mouvement en marche législatives',
            'title' => 'Le mouvement - Législatives',
            'slug' => 'le-mouvement-legislatives',
            'description' => $description,
            'content' => file_get_contents(__DIR__.'/../organization.html'),
            'media' => $media,
        ]));

        $manager->persist($this->pageFactory->createFromArray([
            'keywords' => 'désintox fake news fausse informations',
            'title' => 'Désintox',
            'slug' => 'desintox',
            'description' => $description,
            'content' => file_get_contents(__DIR__.'/../desintox.html'),
            'media' => $media,
        ]));

        $manager->persist($this->pageFactory->createFromArray([
            'keywords' => 'mentions légales',
            'title' => 'Mentions légales',
            'slug' => 'mentions-legales',
            'description' => $description,
            'content' => file_get_contents(__DIR__.'/../legalities.md'),
            'media' => $media,
        ]));

        $manager->persist($this->pageFactory->createFromArray([
            'keywords' => 'politique cookies',
            'title' => 'Politique des Cookies',
            'slug' => 'politique-cookies',
            'description' => $description,
            'content' => file_get_contents(__DIR__.'/../cookies.md'),
            'media' => $media,
        ]));

        $manager->persist($this->pageFactory->createFromArray([
            'keywords' => 'programme propositions',
            'title' => 'Les propositions d\'Emmanuel Macron',
            'slug' => 'emmanuel-macron-propositions',
            'description' => 'Le moment que nous vivons est celui d’une refondation profonde de la France. '.
                'Voici celle que nous vous proposons.',
            'content' => self::$propositions,
            'media' => $media,
        ]));

        $manager->persist($this->pageFactory->createFromArray([
            'keywords' => 'loi travail ordonnances explications',
            'title' => 'Les ordonnances expliquées',
            'slug' => 'les-ordonnances-expliquees',
            'description' => 'Ici vous trouverez les ordonnances expliquées',
            'content' => file_get_contents(__DIR__.'/../explainer.html'),
        ]));

        $manager->persist($this->pageFactory->createFromArray([
            'title' => 'Ça, c\'est du concret !',
            'slug' => 'concrete',
            'description' => $description,
            'content' => file_get_contents(__DIR__.'/../concrete/home.html'),
            'media' => $media,
        ]));

        $manager->persist($this->pageFactory->createFromArray([
            'title' => 'Action talents - Accueil',
            'slug' => 'action-talents',
            'description' => $description,
            'content' => file_get_contents(__DIR__.'/../action-talents/home.html'),
            'media' => $media,
        ]));

        $manager->persist($this->pageFactory->createFromArray([
            'title' => 'Action talents - Candidater',
            'slug' => 'action-talents/candidater',
            'description' => $description,
            'content' => file_get_contents(__DIR__.'/../action-talents/apply.html'),
            'media' => $media,
        ]));

        $manager->persist($this->pageFactory->createFromArray([
            'title' => '1000 Talents',
            'slug' => '1000-talents',
            'description' => 'Faire tomber les barrières de l\'engagement politique.',
            'content' => file_get_contents(__DIR__.'/../1000-talents/home.html'),
            'media' => $media,
        ]));

        $manager->persist($this->pageFactory->createFromArray([
            'title' => 'Carrières',
            'slug' => 'nos-offres',
            'description' => $description,
            'content' => 'Voici nos offres d\'emplois et stages',
            'media' => $media,
        ]));

        $manager->persist($this->pageFactory->createFromArray([
            'title' => 'Test Static Page',
            'slug' => 'emmanuel-macron/test',
            'description' => $description,
            'content' => 'Voici un test pour les pages statiques',
            'media' => $media,
        ]));

        $manager->persist($this->pageFactory->createFromArray([
            'keywords' => 'espace formation',
            'title' => 'Mon parcours de formation',
            'slug' => 'espace-formation',
            'description' => 'Formations pour les animateurs locaux.',
            'content' => file_get_contents(__DIR__.'/../espace-formation/main.html'),
            'media' => $media,
        ]));

        $manager->persist($this->pageFactory->createFromArray([
            'keywords' => 'espace formation',
            'title' => 'Foire aux questions',
            'slug' => 'espace-formation/faq',
            'description' => 'Foire aux questions pour les animateurs locaux.',
            'content' => 'FAQ',
            'media' => $media,
            'layout' => Page::LAYOUT_DEFAULT,
        ]));

        $manager->persist($this->pageFactory->createFromArray([
            'keywords' => 'larem candidats municipales 2020',
            'title' => 'Candidat(e)s investi(e)s et soutenu(e)s par LaREM pour les élections municipales de 2020',
            'slug' => 'municipales/candidats-investis ',
            'description' => $description,
            'content' => file_get_contents(__DIR__.'/../content.md'),
            'media' => $media,
            'layout' => Page::LAYOUT_MUNICIPALES,
        ]));

        $manager->persist($this->pageFactory->createFromArray([
            'keywords' => 'test layout with header image',
            'title' => 'Test du layout avec bannière',
            'slug' => 'test-layout-banniere',
            'description' => 'Cette page s`affiche avec une image d\'en tête',
            'content' => 'Cette page s`affiche avec une image d\'en tête',
            'headerMedia' => $media,
            'layout' => Page::LAYOUT_DEFAULT_WITH_HEADER_IMAGE,
        ]));

        $manager->persist($this->pageFactory->createFromArray([
            'keywords' => 'nous reussirons',
            'title' => 'Nous reussirons (titre non affiché)',
            'slug' => 'nous-reussirons',
            'description' => 'Cette page s`affiche avec une image d\'en tête',
            'content' => 'Cette page s`affiche avec une image d\'en tête',
            'headerMedia' => $media,
            'layout' => Page::LAYOUT_DEFAULT_WITH_HEADER_IMAGE,
        ]));

        $manager->flush();
    }

    private static $nosValeurs = '
Nous croyons au progrès face à tous les conservatismes. Nous croyons que le temps n’est pas aux petits
ajustements mais à l’innovation radicale. Nous croyons en l’émancipation de tous. Nous croyons que le
destin de l’Europe et celui de la France sont indissociables. Nous croyons en notre capacité à agir
ensemble.
    ';

    private static $ceQueJeSuis = '
Il y a, me concernant, des choses que beaucoup savent déjà. J’ai fait l’ENA,
j’ai travaillé dans une banque d’affaires, puis pour François Hollande durant la campagne présidentielle de
2012, et j’ai été à son service durant plus de deux années comme Secrétaire général adjoint de l’Elysée. J’ai
été Ministre de l’Economie, de l’Industrie et du Numérique, avec passion, jusqu’à la fin du mois d’août 2016.
En décembre 2016, j’ai démissionné de la fonction publique. Voilà pour la biographie officielle.
    ';

    private static $propositions = '
<p class="text--body">
    Le moment que nous vivons est celui d’une refondation profonde de la France. Voici celle que nous vous proposons.
    Le rôle de la politique est de déployer un cadre qui permettra à chacune et à chacun de trouver sa voie, de
    devenir maître de son destin, d’exercer sa liberté, de pouvoir choisir sa vie, puis de pouvoir vivre de son
    travail. C’est avec cette promesse d’émancipation que la politique doit renouer. Le chômage n’est pas une
    fatalité, mais un combat que nous pouvons gagner grâce aux mesures juste qui donneront à tous les acteurs la
    capacité de faire face aux changements.
</p>
<p class="text--body text--bold text--medium-small">
    <a href="https://staging.en-marche.fr/articles/actualites/comment-emmanuel-macron-a-t-il-construit-ses-propositions">
        Comment Emmanuel Macron a-t-il construit ses propositions ?
    </a>
</p>
    ';
}
