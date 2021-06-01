<?php

namespace App\Entity\ElectedRepresentative;

use App\Exception\BadSocialLinkTypeException;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(
 *     name="elected_representative_social_network_link",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="social_network_elected_representative_unique", columns={"type", "elected_representative_id"})
 *     }
 * )
 *
 * @UniqueEntity(
 *     fields={"type", "electedRepresentative"},
 *     errorPath="type",
 *     message="Il existe déjà un lien vers ce réseau social pour cet(te) élu(e)"
 * )
 */
class SocialNetworkLink
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Url
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Choice(callback={"App\Entity\ElectedRepresentative\SocialLinkTypeEnum", "toArray"})
     */
    private $type;

    /**
     * @var ElectedRepresentative
     *
     * @ORM\ManyToOne(targetEntity="ElectedRepresentative", inversedBy="socialNetworkLinks")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     *
     * @Assert\NotBlank
     * @Assert\Valid
     */
    private $electedRepresentative;

    public function __construct(
        string $url = null,
        string $type = null,
        ElectedRepresentative $electedRepresentative = null
    ) {
        $this->url = $url;
        $this->type = $type;
        $this->electedRepresentative = $electedRepresentative;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        if (!SocialLinkTypeEnum::isValid($type)) {
            throw new BadSocialLinkTypeException(sprintf('The social link type "%s" is invalid', $type));
        }

        $this->type = $type;
    }

    public function getElectedRepresentative(): ?ElectedRepresentative
    {
        return $this->electedRepresentative;
    }

    public function setElectedRepresentative(ElectedRepresentative $electedRepresentative): void
    {
        $this->electedRepresentative = $electedRepresentative;
    }

    public function __toString(): string
    {
        return $this->getUrl().' ('.$this->type.')';
    }
}
