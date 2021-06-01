<?php

namespace App\DataFixtures\ORM;

use App\Entity\Administrator;
use App\Entity\Filesystem\File;
use App\Entity\Filesystem\FilePermission;
use App\Entity\Filesystem\FilePermissionEnum;
use App\Entity\Filesystem\FileTypeEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadFileData extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $admin = $this->getReference('administrator-1');
        $superadmin = $this->getReference('administrator-2');

        $images = $this->createDirectory('images', $superadmin);
        $videos = $this->createDirectory('videos', $superadmin);
        $documents = $this->createDirectory('documents', $superadmin);
        $hidden = $this->createDirectory('hidden', $superadmin, false);

        $imageCandidates = $this->createFile(
            'Image for candidates',
            $superadmin,
            $images,
            [FilePermissionEnum::CANDIDATE_REGIONAL_HEADED, FilePermissionEnum::CANDIDATE_REGIONAL_LEADER, FilePermissionEnum::CANDIDATE_DEPARTMENTAL],
            'for_candidates.jpg',
            'jpg',
            'image/jpeg',
            65432
        );
        $imageAll = $this->createFile(
            'Image for all',
            $superadmin,
            $images,
            [FilePermissionEnum::ALL],
            'for_all.png',
            'png',
            'image/png',
            5432,
            true,
            '2020-11-12 09:10:11'
        );
        $imageCD = $this->createFile(
            'Image for departmental candidates',
            $admin,
            $images,
            [FilePermissionEnum::CANDIDATE_DEPARTMENTAL],
            'for_departmental.jpg',
            'jpg',
            'image/jpeg',
            43210
        );

        $externalLinkAll = $this->createExternalLink(
            'dpt link for all',
            $superadmin,
            'https://dpt.en-marche.fr',
            $videos,
            [FilePermissionEnum::ALL]
        );

        $externalLinkRC = $this->createExternalLink(
            'external link for regional candidates',
            $admin,
            'https://transformer.en-marche.fr/fr',
            $documents,
            [FilePermissionEnum::CANDIDATE_REGIONAL_LEADER, FilePermissionEnum::CANDIDATE_REGIONAL_HEADED],
            true,
            '2020-11-11 11:11:11'
        );

        $externalLinkHidden = $this->createExternalLink(
            'external hidden link',
            $admin,
            'https://chezvous.en-marche.fr/',
            $documents,
            [FilePermissionEnum::CANDIDATE_REGIONAL_LEADER, FilePermissionEnum::CANDIDATE_REGIONAL_HEADED],
            false,
            '2020-11-01 09:09:09'
        );

        $externalLink = $this->createExternalLink(
            'dpt link',
            $superadmin,
            'https://dpt.en-marche.fr',
            null,
            [FilePermissionEnum::ALL]
        );

        $pdfAll = $this->createFile(
            'PDF for all',
            $superadmin,
            $documents,
            [FilePermissionEnum::ALL],
            'for_all.pdf',
            'pdf',
            'application/pdf',
            12345
        );

        $pdfHidden = $this->createFile(
            'PDF for all hidden',
            $superadmin,
            $documents,
            [FilePermissionEnum::ALL],
            'hidden.pdf',
            'pdf',
            'application/pdf',
            10000,
            false
        );

        $manager->persist($images);
        $manager->persist($videos);
        $manager->persist($documents);
        $manager->persist($hidden);

        $manager->persist($imageCandidates);
        $manager->persist($imageAll);
        $manager->persist($imageCD);

        $manager->persist($externalLinkAll);
        $manager->persist($externalLinkRC);
        $manager->persist($externalLinkHidden);
        $manager->persist($externalLink);

        $manager->persist($pdfAll);
        $manager->persist($pdfHidden);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            LoadAdminData::class,
        ];
    }

    private function createDirectory(string $name, Administrator $admin, bool $displayed = true): File
    {
        return $this->createBaseFile(FileTypeEnum::DIRECTORY, $name, $admin, $displayed);
    }

    private function createExternalLink(
        string $name,
        Administrator $admin,
        string $externalLink,
        ?File $parent,
        array $permissions,
        bool $displayed = true,
        string $createdAt = '2020-11-10 09:08:07'
    ): File {
        $file = $this->createBaseFile(FileTypeEnum::EXTERNAL_LINK, $name, $admin, $displayed, $createdAt, $parent, $permissions);
        $file->setExternalLink($externalLink);

        return $file;
    }

    private function createFile(
        string $name,
        Administrator $admin,
        File $parent,
        array $permissions,
        string $originalFilename,
        string $extension,
        string $mimeType,
        int $size,
        bool $displayed = true,
        string $createdAt = '2020-11-10 09:08:07'
    ): File {
        $file = $this->createBaseFile(FileTypeEnum::FILE, $name, $admin, $displayed, $createdAt, $parent, $permissions);
        $file->setOriginalFilename($originalFilename);
        $file->setExtension($extension);
        $file->setMimeType($mimeType);
        $file->setSize($size);

        return $file;
    }

    private function createBaseFile(
        string $type,
        string $name,
        Administrator $admin,
        bool $displayed = true,
        string $createdAt = '2020-11-10 09:08:07',
        File $parent = null,
        array $permissions = []
    ): File {
        $file = new File();

        $file->setName($name);
        $file->setType($type);
        $file->setCreatedBy($admin);
        $file->setUpdatedBy($admin);
        $file->setCreatedAt(new \DateTime($createdAt));
        $file->setUpdatedAt(new \DateTime($createdAt));
        $file->setDisplayed($displayed);

        if ($parent) {
            $file->setParent($parent);
        }

        foreach ($permissions as $permission) {
            $filePermission = new FilePermission();
            $filePermission->setName($permission);

            $file->addPermission($filePermission);
        }

        return $file;
    }
}
