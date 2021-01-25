<?php

namespace App\Doctrine\DBAL\Platform;

use Doctrine\DBAL\Platforms\PostgreSQL100Platform;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Table;

class PostgreSQLPlatform extends PostgreSQL100Platform
{
    private const FLAG_SPATIAL = 'spatial';

    public function getIndexFieldDeclarationListSQL($columnsOrIndex): string
    {
        if ($columnsOrIndex instanceof Index && $columnsOrIndex->hasFlag(self::FLAG_SPATIAL)) {
            return implode(', ', array_map(
                function ($column) { return sprintf('%s gist_trgm_ops', $column); },
                $columnsOrIndex->getQuotedColumns($this)
            ));
        }

        return parent::getIndexFieldDeclarationListSQL($columnsOrIndex);
    }

    public function getCreateIndexSQL(Index $index, $table)
    {
        if (!$index->hasFlag(self::FLAG_SPATIAL)) {
            return parent::getCreateIndexSQL($index, $table);
        }

        if ($table instanceof Table) {
            $table = $table->getQuotedName($this);
        }

        $table = sprintf('%s USING GIST', $table);

        return parent::getCreateIndexSQL($index, $table);
    }
}
