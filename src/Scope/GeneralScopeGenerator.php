<?php

namespace App\Scope;

use App\Entity\Adherent;
use App\Scope\Exception\InvalidScopeException;
use App\Scope\Exception\NotFoundScopeGeneratorException;
use App\Scope\Generator\ScopeGeneratorInterface;

class GeneralScopeGenerator
{
    /**
     * @var ScopeGeneratorInterface[]|iterable
     */
    private $generators;

    public function __construct(iterable $generators)
    {
        $this->generators = $generators;
    }

    /**
     * @return Scope[]
     */
    public function generateScopes(Adherent $adherent): array
    {
        $scopes = [];

        /** @var ScopeGeneratorInterface $generator */
        foreach ($this->generators as $generator) {
            if ($generator->supports($adherent)) {
                $scopes[] = $generator->generate($adherent);
            }
        }

        return $scopes;
    }

    public function getGenerator(string $scopeCode): ?ScopeGeneratorInterface
    {
        if (!\in_array($scopeCode, ScopeEnum::toArray())) {
            throw new InvalidScopeException(sprintf('Invalid scope "%s"', $scopeCode));
        }

        foreach ($this->generators as $generator) {
            if ($generator->getScope() === $scopeCode) {
                return $generator;
            }
        }

        throw new NotFoundScopeGeneratorException("Scope generator not found for '$scopeCode'");
    }
}
