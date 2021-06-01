<?php

namespace App\Repository;

use App\Entity\Media;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MediaRepository extends ServiceEntityRepository
{
    public const TYPE_IMAGE = 'image';
    public const TYPE_VIDEO = 'video';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Media::class);
    }

    public function findOneByName(string $name): ?Media
    {
        return $this->findOneBy(['name' => $name]);
    }

    public function findOneByPath(string $path): ?Media
    {
        return $this->findOneBy(['path' => $path]);
    }

    /**
     * @return Media[]
     */
    public function findSitemapMedias(string $type): array
    {
        return $this
            ->createQueryBuilder('m')
            ->where('ILIKE(m.mimeType, :type) = true')
            ->setParameter('type', $type.'%')
            ->orderBy('m.id')
            ->getQuery()
            ->getResult()
        ;
    }
}
