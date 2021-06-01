<?php

namespace App\Doctrine\Filter;

use App\Entity\EnabledInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class EnabledFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        if (!$targetEntity->reflClass->implementsInterface(EnabledInterface::class)) {
            return '';
        }

        return $targetTableAlias.'.enabled = true';
    }
}
