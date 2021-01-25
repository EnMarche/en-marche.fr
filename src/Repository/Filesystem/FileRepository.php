<?php

namespace App\Repository\Filesystem;

use App\Entity\Filesystem\File;
use App\Entity\Filesystem\FilePermissionEnum;
use App\Entity\Filesystem\FileTypeEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class FileRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, File::class);
    }

    public function findForAutocomplete(string $search): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.type = :directory')
            ->andWhere('f.displayed = true')
            ->andWhere('LOWER(f.name) LIKE :search')
            ->setParameter('directory', FileTypeEnum::DIRECTORY)
            ->setParameter('search', '%'.$search.'%')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findDirectoryByName($value): ?File
    {
        $name = \is_array($value) ? $value['name'] : $value;

        return $this->createQueryBuilder('file')
            ->where('file.type = :dir_type AND file.name = :name')
            ->setParameters([
                'name' => $name,
                'dir_type' => FileTypeEnum::DIRECTORY,
            ])
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findWithPermissionsInDirectory(
        array $permissions,
        File $directory = null,
        string $order = 'a'
    ): array {
        $qb = $this->createWithPermissionsQueryBuilder($permissions)
            ->andWhere('file.displayed = 1 ')
            ->orderBy('file.name', 'd' === $order ? 'DESC' : 'ASC')
        ;

        if ($directory) {
            $qb
                ->andWhere('file.parent = :directory')
                ->setParameter('directory', $directory)
            ;
        } else {
            $qb
                ->andWhere('file.parent IS NULL')
            ;
        }

        return $qb->getQuery()->getResult();
    }

    public function createWithPermissionsQueryBuilder(array $permissions): QueryBuilder
    {
        array_push($permissions, FilePermissionEnum::ALL);

        return $this->createQueryBuilder('file')
            ->leftJoin('file.permissions', 'permission')
            ->leftJoin('file.children', 'child')
            ->leftJoin('child.permissions', 'childPermission')
            ->where('(permission.name IN (:permissions) OR childPermission.name IN (:permissions))')
            ->setParameter('permissions', $permissions)
        ;
    }
}
