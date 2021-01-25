<?php

namespace App\Assessor\Filter;

use App\Exception\AssessorException;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

class AssessorRequestFilters extends AssessorFilters
{
    public const PROCESSED = 'processed';
    public const UNPROCESSED = 'unprocessed';
    public const DISABLED = 'disabled';
    public const PARAMETER_LAST_NAME = 'lastName';

    private $lastName;

    public static function fromRequest(Request $request)
    {
        $filters = parent::fromRequest($request);
        $filters->setStatus($request->query->get(self::PARAMETER_STATUS, self::UNPROCESSED));

        if ($lastName = $request->query->get(self::PARAMETER_LAST_NAME)) {
            $filters->setLastname($lastName);
        }

        return $filters;
    }

    public function setStatus(string $status): void
    {
        $status = mb_strtolower(trim($status));

        if ($status && !\in_array($status, [self::PROCESSED, self::UNPROCESSED, self::DISABLED], true)) {
            throw new AssessorException(sprintf('Unexpected assessor request status "%s".', $status));
        }

        parent::setStatus($status);
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function hasData(): bool
    {
        return parent::hasData() || $this->lastName;
    }

    public function apply(QueryBuilder $qb, string $alias): void
    {
        parent::apply($qb, $alias);

        $qb->andWhere("$alias.enabled = :enabled");

        if ($this->isStatusUnprocessed()) {
            $qb
                ->andWhere("$alias.processed = :processed AND $alias.processedAt IS NULL")
                ->setParameter('processed', false)
                ->setParameter('enabled', true)
            ;
        } elseif (self::DISABLED === $this->getStatus()) {
            $qb
                ->setParameter('enabled', false)
            ;
        } else {
            $qb
                ->andWhere("$alias.processed = :processed AND $alias.processedAt IS NOT NULL")
                ->setParameter('processed', true)
                ->setParameter('enabled', true)
            ;
        }

        if ($this->getLastName()) {
            $qb
                ->andWhere("$alias.lastName = :lastName")
                ->setParameter('lastName', $this->getLastName())
            ;
        }

        if ($this->getCity()) {
            if (is_numeric($this->getCity())) {
                $qb
                    ->andWhere("$alias.assessorPostalCode LIKE :assessorPostalCode")
                    ->setParameter('assessorPostalCode', $this->getCity().'%')
                ;
            } else {
                $qb
                    ->andWhere("ILIKE($alias.assessorCity, :assessorCity) = true")
                    ->setParameter('assessorCity', '%'.$this->getCity().'%')
                ;
            }
        }

        if ($this->getCountry()) {
            $qb
                ->andWhere("$alias.assessorCountry = :assessorCountry")
                ->setParameter('assessorCountry', $this->getCountry())
            ;
        }

        if ($this->getVotePlace()) {
            if ($this->isStatusUnprocessed()) {
                $qb
                    ->innerJoin("$alias.votePlaceWishes", 'vp')
                    ->andWhere("$alias.votePlace IS NULL")
                ;
            } else {
                $qb->innerJoin("$alias.votePlace", 'vp');
            }

            if (preg_match(AssessorFilters::VOTE_PLACE_CODE_REGEX, $this->getVotePlace())) {
                $qb
                    ->andWhere('vp.code = :code')
                    ->setParameter('code', $this->getVotePlace())
                ;
            } else {
                $qb
                    ->andWhere('ILIKE(vp.name, :name)')
                    ->setParameter('name', '%'.strtolower($this->getVotePlace()).'%')
                ;
            }
        }

        $qb
            ->orderBy("$alias.processed", 'ASC')
            ->addOrderBy("$alias.createdAt", 'DESC')
            ->addOrderBy("$alias.lastName", 'ASC')
        ;
    }

    public function isStatusUnprocessed(): bool
    {
        return self::UNPROCESSED === $this->getStatus();
    }
}
