<?php

namespace App\Procuration\Filter;

use App\Exception\ProcurationException;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

class ProcurationRequestFilters extends ProcurationFilters
{
    public const PROCESSED = 'processed';
    public const UNPROCESSED = 'unprocessed';

    public static function fromQueryString(Request $request)
    {
        $filters = parent::fromQueryString($request);
        $filters->setStatus($request->query->get(self::PARAMETER_STATUS, self::UNPROCESSED));

        return $filters;
    }

    public function setStatus(string $status): void
    {
        $status = mb_strtolower(trim($status));

        if ($status && !\in_array($status, [self::PROCESSED, self::UNPROCESSED], true)) {
            throw new ProcurationException(sprintf('Unexpected procuration request status "%s".', $status));
        }

        parent::setStatus($status);
    }

    public function apply(QueryBuilder $qb, string $alias): void
    {
        parent::apply($qb, $alias);

        if ($this->matchUnprocessedRequests()) {
            $qb
                ->andWhere("$alias.processed = :flag AND $alias.processedAt IS NULL")
                ->setParameter('flag', false)
            ;
        } else {
            $qb
                ->andWhere("$alias.processed = :flag AND $alias.processedAt IS NOT NULL")
                ->setParameter('flag', true)
            ;
        }

        $qb
            ->orderBy("$alias.processed", 'ASC')
            ->addOrderBy("$alias.createdAt", 'DESC')
            ->addOrderBy("$alias.lastName", 'ASC')
        ;
    }

    public function matchUnprocessedRequests(): bool
    {
        return self::UNPROCESSED === $this->getStatus();
    }
}
