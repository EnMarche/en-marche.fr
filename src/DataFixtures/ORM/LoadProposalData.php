<?php

namespace App\DataFixtures\ORM;

use App\Content\MediaFactory;
use App\Content\ProposalFactory;
use App\Entity\ProposalTheme;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use League\Flysystem\FilesystemInterface;
use Symfony\Component\HttpFoundation\File\File;

class LoadProposalData extends Fixture
{
    private $proposalFactory;
    private $mediaFactory;
    private $storage;

    public function __construct(
        ProposalFactory $proposalFactory,
        MediaFactory $mediaFactory,
        FilesystemInterface $storage
    ) {
        $this->proposalFactory = $proposalFactory;
        $this->mediaFactory = $mediaFactory;
        $this->storage = $storage;
    }

    public function load(ObjectManager $manager)
    {
        // Media
        $mediaFile = new File(__DIR__.'/../../../app/data/dist/guadeloupe.jpg');
        $this->storage->put('images/proposal.jpg', file_get_contents($mediaFile->getPathname()));
        $media = $this->mediaFactory->createFromFile('Proposition image', 'proposal.jpg', $mediaFile);
        $manager->persist($media);

        $manager->flush();

        // Themes
        $manager->persist($education = new ProposalTheme('Education'));
        $manager->persist($environment = new ProposalTheme('Environnement'));
        $manager->persist($work = new ProposalTheme('Travail'));
        $manager->persist($solidarity = new ProposalTheme('Solidarité'));
        $manager->persist($agriculture = new ProposalTheme('Agriculture'));
        $manager->persist($socialProtection = new ProposalTheme('Protection sociale'));

        $manager->flush();

        // Proposals
        $manager->persist($this->proposalFactory->createFromArray([
            'position' => 1,
            'title' => 'Produire en France et sauver la planète',
            'slug' => 'produire-en-france-et-sauver-la-planete',
            'description' => 'Produire en France et sauver la planète',
            'media' => $media,
            'displayMedia' => true,
            'published' => true,
            'themes' => [$agriculture, $environment],
            'content' => file_get_contents(__DIR__.'/../content.md'),
            'amp_content' => file_get_contents(__DIR__.'/../content_amp.html'),
        ]));

        $manager->persist($this->proposalFactory->createFromArray([
            'position' => 2,
            'title' => 'Mieux vivre de son travail',
            'slug' => 'mieux-vivre-de-son-travail',
            'description' => 'Mieux vivre de son travail',
            'media' => $media,
            'displayMedia' => false,
            'published' => false,
            'themes' => [$work],
            'content' => file_get_contents(__DIR__.'/../content.md'),
            'amp_content' => file_get_contents(__DIR__.'/../content_amp.html'),
        ]));

        $manager->persist($this->proposalFactory->createFromArray([
            'position' => 3,
            'title' => 'Eduquer tous nos enfants',
            'slug' => 'eduquer-tous-nos-enfants',
            'description' => 'Eduquer tous nos enfants',
            'media' => $media,
            'displayMedia' => true,
            'published' => true,
            'themes' => [$education],
            'content' => file_get_contents(__DIR__.'/../content.md'),
            'amp_content' => file_get_contents(__DIR__.'/../content_amp.html'),
        ]));

        $manager->flush();
    }
}
