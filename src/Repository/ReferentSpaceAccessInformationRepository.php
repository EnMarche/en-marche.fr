<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Adherent;
use AppBundle\Entity\ReferentSpaceAccessInformation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ReferentSpaceAccessInformationRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, ReferentSpaceAccessInformation::class);
    }

    public function findByAdherent(Adherent $adherent): ?ReferentSpaceAccessInformation
    {
        return $this->findOneBy(['adherent' => $adherent]);
    }
}
