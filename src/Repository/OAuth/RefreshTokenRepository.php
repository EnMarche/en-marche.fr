<?php

namespace App\Repository\OAuth;

use App\Entity\Adherent;
use App\Entity\OAuth\Client;
use App\Entity\OAuth\RefreshToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Ramsey\Uuid\UuidInterface;

class RefreshTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RefreshToken::class);
    }

    public function save(RefreshToken $token): void
    {
        $this->_em->persist($token);
        $this->_em->flush();
    }

    public function findRefreshTokenByIdentifier(string $identifier): ?RefreshToken
    {
        return $this->findOneBy(['identifier' => $identifier]);
    }

    public function findRefreshTokenByUuid(UuidInterface $uuid): ?RefreshToken
    {
        return $this->findOneBy(['uuid' => $uuid->toString()]);
    }

    /**
     * @return RefreshToken[]
     */
    public function findAllRefreshTokensByClient(Client $client): array
    {
        return $this
            ->createQueryBuilder('rt')
            ->join('rt.accessToken', 'at')
            ->where('at.client = :client')
            ->setParameter('client', $client)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return RefreshToken[]
     */
    public function findAllRefreshTokensByUser(Adherent $user): array
    {
        return $this
            ->createQueryBuilder('rt')
            ->join('rt.accessToken', 'at')
            ->where('at.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult()
        ;
    }

    public function revokeClientTokens(Client $client): void
    {
        foreach ($this->findAllRefreshTokensByClient($client) as $refreshToken) {
            $this->revokeToken($refreshToken);
        }
    }

    public function revokeUserTokens(Adherent $user): void
    {
        foreach ($this->findAllRefreshTokensByUser($user) as $refreshToken) {
            $this->revokeToken($refreshToken);
        }
    }

    private function revokeToken(RefreshToken $token): void
    {
        if (!$token->isRevoked()) {
            $token->revoke();
        }
    }
}
