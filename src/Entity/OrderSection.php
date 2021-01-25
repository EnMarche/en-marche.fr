<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Facebook\GraphNodes\Collection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="order_sections")
 * @ORM\Entity(repositoryClass="App\Repository\OrderSectionRepository")
 */
class OrderSection
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
     * @ORM\Column(length=50)
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint")
     *
     * @Assert\NotBlank
     */
    private $position;

    /**
     * @var OrderArticle[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\OrderArticle", mappedBy="sections")
     * @ORM\OrderBy({"position": "ASC"})
     */
    private $articles;

    public function __construct(int $position = 1, string $name = '')
    {
        $this->position = $position;
        $this->name = $name;
    }

    public function __toString()
    {
        return $this->name ?: '';
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition($position): void
    {
        $this->position = $position;
    }

    /**
     * @return OrderArticle[]|Collection
     */
    public function getArticles(): iterable
    {
        return $this->articles;
    }

    public function getPublishedArticlesOrderedByPosition(): iterable
    {
        if ($this->articles->count() > 0) {
            return $this->articles->filter(function (OrderArticle $article) {
                return $article->isPublished();
            });
        } else {
            return $this->articles;
        }
    }
}
