<?php

namespace App\Doctrine\Query\PostgreSQL;

use MartinGeorgiev\Doctrine\ORM\Query\AST\Functions\BaseFunction;

class Unaccent extends BaseFunction
{
    protected function customiseFunction(): void
    {
        $this->setFunctionPrototype('unaccent(%s)');
        $this->addNodeMapping('StringPrimary');
    }
}
