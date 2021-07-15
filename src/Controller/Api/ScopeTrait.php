<?php

namespace App\Controller\Api;

use App\Entity\Adherent;
use App\Scope\Exception\NotFoundScopeGeneratorException;
use App\Scope\GeneralScopeGenerator;
use App\Scope\Scope;

trait ScopeTrait
{
    /** @var GeneralScopeGenerator */
    protected $generalScopeGenerator;

    protected function getScope(string $scopeCode, Adherent $adherent): Scope
    {
        try {
            $generator = $this->generalScopeGenerator->getGenerator($scopeCode);

            if ($generator->supports($adherent)) {
                return $generator->generate($adherent);
            }
        } catch (NotFoundScopeGeneratorException $e) {
            // Catch for throwing AccessDenied exception
        }

        throw $this->createAccessDeniedException('User has no required scope.');
    }
}
