<?php

namespace AppBundle\Entity;

use Takeit\Bundle\AmpHtmlBundle\Model\AmpInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="articles")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ArticleRepository")
 *
 * @UniqueEntity(fields={"slug"})
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Article implements AmpInterface
{
    use EntityTimestampableTrait;
    use EntitySoftDeletableTrait;
    use EntityContentTrait;
    use EntityMediaTrait;
    use EntityPublishableTrait;

    /**
     * @var int
     *
     * @ORM\Column(type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var ArticleCategory|null
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\ArticleCategory")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", onDelete="SET NULL")
     *
     * @Assert\NotBlank
     */
    private $category;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     *
     * @Assert\NotBlank
     */
    private $publishedAt;

    /**
     * @var ProposalTheme[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="ProposalTheme")
     */
    private $themes;

    public function __construct()
    {
        $this->publishedAt = new \DateTime();
        $this->themes = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return ArticleCategory|null
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param ArticleCategory|null $category
     *
     * @return Article
     */
    public function setCategory(ArticleCategory $category = null): Article
    {
        $this->category = $category;

        return $this;
    }

    public function getPublishedAt(): \DateTime
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(\DateTime $publishedAt): Article
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function addTheme(ProposalTheme $theme)
    {
        $this->themes[] = $theme;
    }

    public function removeTheme(ProposalTheme $theme)
    {
        $this->themes->removeElement($theme);
    }

    /**
     * @return ProposalTheme[]|Collection
     */
    public function getThemes()
    {
        return $this->themes;
    }
}
