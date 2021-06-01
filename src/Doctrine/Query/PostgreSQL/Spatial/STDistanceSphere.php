<?php

namespace App\Doctrine\Query\PostgreSQL\Spatial;

use CrEOF\Spatial\ORM\Query\AST\Functions\PostgreSql\STDistanceSphere as BaseSTDistanceSphere;

class STDistanceSphere extends BaseSTDistanceSphere
{
    protected $functionName = 'ST_DistanceSphere';
}
