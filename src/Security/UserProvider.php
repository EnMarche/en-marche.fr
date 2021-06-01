<?php

namespace App\Security;

use App\Repository\FailedLoginAttemptRepository;
use App\Security\Exception\BadCredentialsException;
use App\Security\Exception\MaxLoginAttemptException;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Security\User\EntityUserProvider;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class UserProvider extends EntityUserProvider
{
    private $failedLoginAttemptRepository;
    private $requestStack;
    private $logger;

    public function __construct(
        ManagerRegistry $registry,
        $classOrAlias,
        $property = null,
        $managerName = null,
        FailedLoginAttemptRepository $failedLoginAttemptRepository,
        RequestStack $requestStack,
        LoggerInterface $logger
    ) {
        parent::__construct($registry, $classOrAlias, $property, $managerName);

        $this->failedLoginAttemptRepository = $failedLoginAttemptRepository;
        $this->requestStack = $requestStack;
        $this->logger = $logger;
    }

    public function loadUserByUsername($username)
    {
        $signature = LoginAttemptSignature::createFromRequest($this->requestStack->getMasterRequest())->getSignature();

        if (!$this->failedLoginAttemptRepository->canLogin($signature)) {
            $this->logger->warning(sprintf('Max login attempts reached for "%s"', $username), [
                'attempts' => $this->failedLoginAttemptRepository->countAttempts($signature),
                'username' => $username,
                'signature' => $signature,
            ]);

            throw new MaxLoginAttemptException();
        }

        try {
            return parent::loadUserByUsername($username);
        } catch (UsernameNotFoundException $e) {
            // security.hide_user_not_found option is disabled in order to customize the error message
            // So we must handle that logic ourself
            throw new BadCredentialsException(sprintf('Username %s not found.', $username), 0, $e);
        }
    }
}
