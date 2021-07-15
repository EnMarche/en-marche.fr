<?php

namespace App\Scope\Generator;

use App\Entity\Adherent;
use App\Entity\Scope as ScopeEntity;
use App\Repository\ScopeRepository;
use App\Scope\Scope;

abstract class AbstractScopeGenerator implements ScopeGeneratorInterface
{
    private $scopeRepository;

    public function __construct(ScopeRepository $scopeRepository)
    {
        $this->scopeRepository = $scopeRepository;
    }

    final public function generate(Adherent $adherent): Scope
    {
        $code = $this->getScope();

        $scopeEntity = $this->findScope($code);

        return new Scope(
            $code,
            $scopeEntity->getName(),
            $this->getZones($adherent),
            $scopeEntity->getApps(),
            $scopeEntity->getFeatures()
        );
    }

    abstract protected function getZones(Adherent $adherent): array;

    private function findScope(string $code): ?ScopeEntity
    {
        $scope = $this->scopeRepository->findOneByCode($code);

        if (!$scope) {
            throw new \InvalidArgumentException(sprintf('Could not find any Scope with code "%s" in database.', $code));
        }

        return $scope;
    }
}
