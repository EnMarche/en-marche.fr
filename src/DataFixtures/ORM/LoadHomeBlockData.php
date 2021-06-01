<?php

namespace App\DataFixtures\ORM;

use App\Content\HomeBlockFactory;
use App\Content\MediaFactory;
use App\Entity\HomeBlock;
use App\Entity\Media;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\HttpFoundation\File\File;

class LoadHomeBlockData extends Fixture
{
    private $mediaFactory;
    private $homeBlockFactory;
    private $storage;

    /**
     * @var array
     */
    private $mediasRegistry;

    public function __construct(
        MediaFactory $mediaFactory,
        HomeBlockFactory $homeBlockFactory,
        FilesystemInterface $storage
    ) {
        $this->mediaFactory = $mediaFactory;
        $this->homeBlockFactory = $homeBlockFactory;
        $this->storage = $storage;
    }

    public function load(ObjectManager $manager)
    {
        $this->loadMedias($manager);
        $this->loadHomeBlocks($manager);
    }

    private function loadMedias(ObjectManager $manager): void
    {
        $repository = $manager->getRepository(Media::class);

        foreach (self::$data as $homeBlockData) {
            if (!\array_key_exists('path', $homeBlockData)) {
                continue;
            }

            if (isset($this->mediasRegistry[$homeBlockData['path']])) {
                continue;
            }

            if ($media = $repository->findOneBy(['path' => $homeBlockData['path']])) {
                $this->mediasRegistry[$media->getPath()] = $media;

                continue;
            }

            $mediaFile = new File(__DIR__.'/../../../app/data/dist/'.$homeBlockData['path']);
            $this->storage->put('images/'.$homeBlockData['path'], file_get_contents($mediaFile->getPathname()));

            $media = $this->mediaFactory->createFromFile(
                $homeBlockData['pathTitle'],
                $homeBlockData['path'],
                $mediaFile
            );

            $manager->persist($media);

            $this->mediasRegistry[$media->getPath()] = $media;
        }

        $manager->flush();
    }

    private function loadHomeBlocks(ObjectManager $manager): void
    {
        $repository = $manager->getRepository(HomeBlock::class);

        foreach (self::$data as $i => $homeBlockData) {
            if ($repository->findOneBy(['position' => $i])) {
                continue;
            }

            $manager->persist($this->homeBlockFactory->createFromArray([
                'position' => $i,
                'positionName' => $homeBlockData['positionName'],
                'type' => $homeBlockData['type'],
                'title' => $homeBlockData['title'],
                'subtitle' => $homeBlockData['subtitle'] ?? '',
                'link' => $homeBlockData['link'],
                'media' => \array_key_exists('path', $homeBlockData) ? $this->mediasRegistry[$homeBlockData['path']] : null,
                'titleCta' => $homeBlockData['titleCta'] ?? null,
                'colorCta' => $homeBlockData['colorCta'] ?? null,
                'bgColor' => $homeBlockData['bgColor'] ?? null,
            ]));
        }

        $manager->flush();
    }

    private static $data = [
        [
            'positionName' => 'Bannière - Gauche',
            'type' => 'article',
            'title' => '',
            'path' => 'bandeau-PR-Ensemble-nous-reussirons.jpeg',
            'pathTitle' => 'Guadeloupe',
            'link' => 'http://enmarche.code',
        ],
        [
            'positionName' => 'Bannière - Droite',
            'type' => 'article',
            'title' => '',
            'path' => 'logo-france-relance.jpeg',
            'pathTitle' => 'Carte de France',
            'link' => 'http://enmarche.code',
        ],
        [
            'positionName' => 'Grille - Bloc 1',
            'type' => 'article',
            'title' => 'Tribune de Richard Ferrand',
            'subtitle' => null,
            'path' => 'richardferrand.jpg',
            'pathTitle' => 'Richard Ferrand',
            'link' => 'http://www.richardferrand.fr/2016/12/quand-le-ministre-eckert-fait-expres-ou-pas-de-ne-pas-comprendre/',
        ],
        [
            'positionName' => 'Grille - Bloc 2',
            'type' => 'video',
            'title' => 'Signez l’appel « Elles Marchent »',
            'subtitle' => null,
            'path' => 'ellesmarchent.jpg',
            'pathTitle' => 'Elles marchent',
            'link' => 'https://en-marche.fr/signez-lappel-marchent/',
        ],
        [
            'positionName' => 'Grille - Bloc 3',
            'type' => 'video',
            'title' => 'Revivez le grand rassemblement du 10 décembre !',
            'subtitle' => null,
            'path' => '10decembre.jpg',
            'pathTitle' => '10 décembre',
            'link' => 'https://en-marche.fr/rendez-10-decembre-a-paris/',
        ],
        [
            'positionName' => 'Grille - Bloc 4',
            'type' => 'article',
            'title' => 'Tribune de Richard Ferrand',
            'subtitle' => null,
            'path' => 'macron.jpg',
            'pathTitle' => 'Macron',
            'link' => 'http://www.richardferrand.fr/2016/12/quand-le-ministre-eckert-fait-expres-ou-pas-de-ne-pas-comprendre/',
        ],
        [
            'positionName' => 'Grille - Bloc 5',
            'type' => 'video',
            'title' => 'Signez l’appel « Elles Marchent »',
            'subtitle' => null,
            'path' => 'ellesmarchent.jpg',
            'pathTitle' => 'Elles marchent',
            'link' => 'https://en-marche.fr/signez-lappel-marchent/',
        ],
        [
            'positionName' => 'Grille - Bloc 6',
            'type' => 'video',
            'title' => 'Revivez le grand rassemblement du 10 décembre !',
            'subtitle' => null,
            'path' => '10decembre.jpg',
            'pathTitle' => '10 décembre',
            'link' => 'https://en-marche.fr/rendez-10-decembre-a-paris/',
        ],
        [
            'positionName' => 'Bannière bas de page',
            'type' => 'article',
            'title' => '3 boucliers pour protéger la France',
            'subtitle' => null,
            'path' => 'proteger-la-france.jpg',
            'pathTitle' => '3 boucliers pour protéger la France',
            'link' => 'https://en-marche.fr/rendez-10-decembre-a-paris/',
        ],
        [
            'positionName' => 'Bannière gauche sous la cover',
            'type' => 'banner',
            'title' => 'Je participe à un événement',
            'link' => '/evenements',
            'titleCta' => 'Voir la carte',
            'colorCta' => 'white',
            'bgColor' => 'pink',
        ],
        [
            'positionName' => 'Bannière droite sous la cover',
            'type' => 'banner',
            'title' => 'Je rejoins un comité local',
            'link' => 'https://appel.en-marche.fr',
            'titleCta' => 'J\'y vais',
            'colorCta' => 'white',
            'bgColor' => 'blue',
        ],
        [
            'positionName' => 'Bannière en haut',
            'type' => 'banner',
            'title' => 'Une idée pour changer la politique ?',
            'link' => 'https://appel.en-marche.fr',
            'titleCta' => 'Laissez un message',
            'colorCta' => 'black',
            'bgColor' => 'green',
        ],
    ];
}
