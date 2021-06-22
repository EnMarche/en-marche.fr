<?php

namespace App\Doctrine\Filter;

use App\Entity\EnabledInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class EnabledFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias)
    {
        if (!is_a($targetEntity->getName(), EnabledInterface::class, true)) {
            return '';
        }

        return $targetTableAlias.'.enabled = 1';
    }
}
