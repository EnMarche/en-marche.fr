<?php

namespace AppBundle\Entity;

use Algolia\AlgoliaSearchBundle\Mapping\Annotation as Algolia;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

trait EntityNameSlugTrait
{
    /**
     * The group name.
     *
     * @ORM\Column
     *
     * @Algolia\Attribute
     *
     * @JMS\Groups({"public", "committee_read", "citizen_project_read"})
     *
     * @Assert\NotBlank(groups={"idea_post", "idea_put"})
     * @Assert\Length(max=30, groups={"idea_post", "idea_put"})
     */
    protected $name;

    /**
     * The group name.
     *
     * @ORM\Column
     *
     * @Algolia\Attribute
     */
    protected $canonicalName;

    /**
     * The group slug.
     *
     * @ORM\Column
     *
     * @Gedmo\Slug(fields={"canonicalName"})
     *
     * @Algolia\Attribute
     *
     * @JMS\Groups({"public", "committee_read", "citizen_project_read"})
     */
    protected $slug;

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setName(string $name)
    {
        $this->name = $name;
        $this->canonicalName = static::canonicalize($name);
    }

    public static function canonicalize(string $name): string
    {
        return mb_strtolower($name);
    }

    public function updateSlug(string $slug): void
    {
        $this->slug = $slug;
    }
}
