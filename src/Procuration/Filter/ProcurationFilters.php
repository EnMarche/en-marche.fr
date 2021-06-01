<?php

namespace App\Procuration\Filter;

use App\Exception\ProcurationException;
use App\Intl\UnitedNationsBundle;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

abstract class ProcurationFilters
{
    public const PARAMETER_CITY = 'city';
    public const PARAMETER_COUNTRY = 'country';
    public const PARAMETER_PAGE = 'page';
    public const PARAMETER_ELECTION_ROUND = 'round';
    public const PARAMETER_STATUS = 'status';
    public const PARAMETER_LAST_NAME = 'last_name';

    private const PER_PAGE = 30;

    private $currentPage;
    private $country;
    private $city;
    private $electionRound;
    private $status;
    private $lastName;

    final private function __construct()
    {
    }

    public static function fromQueryString(Request $request)
    {
        $filters = new static();

        if ($country = $request->query->get(self::PARAMETER_COUNTRY)) {
            $filters->setCountry($country);
        }

        if ($city = $request->query->get(self::PARAMETER_CITY)) {
            $filters->setCity($city);
        }

        if ($round = $request->query->get(self::PARAMETER_ELECTION_ROUND)) {
            $filters->setElectionRound($round);
        }

        if ($status = $request->query->get(self::PARAMETER_STATUS)) {
            $filters->setStatus($status);
        }

        if ($page = $request->query->getInt(self::PARAMETER_PAGE, 1)) {
            $filters->setCurrentPage($page);
        }

        if ($lastName = $request->query->get(self::PARAMETER_LAST_NAME)) {
            $filters->setLastName($lastName);
        }

        return $filters;
    }

    public function setCurrentPage(int $page): void
    {
        if ($page < 1) {
            $page = 1;
        }

        $this->currentPage = $page;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    final public function getLimit(): int
    {
        return self::PER_PAGE;
    }

    public function setCountry(string $country): void
    {
        if (empty($country = strtoupper(trim($country)))) {
            $this->country = null;

            return;
        }

        if (!\in_array($country, array_keys($this->getCountries()), true)) {
            throw new ProcurationException(sprintf('Invalid country filter value given ("%s").', $country));
        }

        $this->country = trim($country);
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCity(string $city): void
    {
        $this->city = trim($city);
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setElectionRound(?int $round): void
    {
        $this->electionRound = $round;
    }

    public function getElectionRound(): ?int
    {
        return $this->electionRound;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status ?: null;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getCountries(): array
    {
        return UnitedNationsBundle::getCountries();
    }

    public function hasData(): bool
    {
        return $this->country || $this->city || $this->electionRound || $this->lastName;
    }

    public function apply(QueryBuilder $qb, string $alias): void
    {
        if ($this->country) {
            $qb
                ->andWhere("$alias.voteCountry = :filterVotreCountry")
                ->setParameter('filterVotreCountry', $this->country)
            ;
        }

        if ($this->city) {
            if (is_numeric($this->city)) {
                $qb
                    ->andWhere("$alias.votePostalCode LIKE :filterVoteCity")
                    ->setParameter('filterVoteCity', $this->city.'%')
                ;
            } else {
                $qb
                    ->andWhere("LOWER($alias.voteCityName) LIKE :filterVoteCity")
                    ->setParameter('filterVoteCity', '%'.mb_strtolower($this->city).'%')
                ;
            }
        }

        $qb
            ->leftJoin("$alias.electionRounds", 'rounds')
            ->andWhere('rounds.date >= CURRENT_DATE()')
        ;

        if ($this->electionRound) {
            $qb
                ->andWhere(":round MEMBER OF $alias.electionRounds")
                ->setParameter('round', $this->electionRound)
            ;
        }

        if ($this->lastName) {
            $qb
                ->andWhere("ILIKE($alias.lastName, :last_name) = true")
                ->setParameter('last_name', '%'.mb_strtolower($this->lastName).'%')
            ;
        }

        $qb
            ->setFirstResult(($this->currentPage - 1) * self::PER_PAGE)
            ->setMaxResults(self::PER_PAGE)
        ;
    }

    final public function toQueryString(): string
    {
        return http_build_query($this->getQueryStringParameters());
    }

    protected function getQueryStringParameters(): array
    {
        if ($this->country) {
            $parameters[self::PARAMETER_COUNTRY] = mb_strtolower($this->country);
        }

        if ($this->city) {
            $parameters[self::PARAMETER_CITY] = $this->city;
        }

        if ($this->electionRound) {
            $parameters[self::PARAMETER_ELECTION_ROUND] = $this->electionRound;
        }

        if ($this->status) {
            $parameters[self::PARAMETER_STATUS] = $this->status;
        }

        if ($this->lastName) {
            $parameters[self::PARAMETER_LAST_NAME] = $this->lastName;
        }

        return $parameters ?? [];
    }
}
