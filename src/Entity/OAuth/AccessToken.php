<?php

namespace AppBundle\Entity\OAuth;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\OAuth\AccessTokenRepository")
 * @ORM\Table(name="oauth_access_tokens", uniqueConstraints={
 *   @ORM\UniqueConstraint(name="oauth_access_tokens_uuid_unique", columns="uuid"),
 *   @ORM\UniqueConstraint(name="oauth_access_tokens_identifier_unique", columns="identifier")
 * })
 */
class AccessToken extends AbstractGrantToken
{
}
