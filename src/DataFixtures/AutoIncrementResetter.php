<?php

namespace App\DataFixtures;

use Doctrine\ORM\EntityManagerInterface;

class AutoIncrementResetter
{
    public static function resetAutoIncrement(EntityManagerInterface $manager, string $tableName): void
    {
        $manager->getConnection()->exec('ALTER SEQUENCE '.$tableName.'_id_seq RESTART;');
    }
}
