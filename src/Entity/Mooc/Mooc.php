<?php

namespace App\Entity\Mooc;

use App\Entity\EntityTimestampableTrait;
use App\Entity\Image;
use App\Validator\ImageObject as AssertImageObject;
use Cake\Chronos\MutableDate;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as JMS;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MoocRepository")
 * @ORM\Table(
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="mooc_slug", columns="slug")
 *     }
 * )
 *
 * @UniqueEntity("title")
 * @Assert\Expression(
 *     expression="(this.getArticleImage() and null === this.getYoutubeId()) or (this.getYoutubeId() and null === this.getArticleImage())",
 *     message="mooc.two_media"
 * )
 */
class Mooc
{
    use EntityTimestampableTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     *
     * @JMS\Groups({"mooc_list"})
     */
    private $title;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @JMS\Groups({"mooc_list"})
     */
    private $description;

    /**
     * @ORM\Column
     * @Gedmo\Slug(fields={"title"}, unique=true)
     *
     * @JMS\Groups({"mooc_list"})
     */
    private $slug;

    /**
     * @var Chapter[]|Collection
     *
     * @ORM\OneToMany(targetEntity="Chapter", mappedBy="mooc", cascade={"all"})
     * @ORM\OrderBy({"position": "ASC"})
     *
     * @Assert\Valid
     */
    private $chapters;

    /**
     * @ORM\Column(length=800, nullable=true)
     *
     * @Assert\Length(min=5, max=800)
     */
    private $content;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @Assert\Regex(pattern="/^[A-Za-z0-9_-]+$/", message="mooc.youtubeid_syntax")
     * @Assert\Length(min=2, max=11)
     */
    private $youtubeId;

    /**
     * @var \DateTimeInterface|null
     *
     * @ORM\Column(type="time", nullable=true)
     *
     * @Assert\Time
     */
    private $youtubeDuration;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $shareTwitterText;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $shareFacebookText;

    /**
     * @ORM\Column
     *
     * @Assert\NotBlank
     * @Assert\Length(max=255)
     */
    private $shareEmailSubject;

    /**
     * @ORM\Column(length=500)
     *
     * @Assert\NotBlank
     * @Assert\Length(min=5, max=500)
     */
    protected $shareEmailBody;

    /**
     * @var Image|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Image", cascade={"all"}, orphanRemoval=true)
     *
     * @AssertImageObject(
     *     mimeTypes={"image/jpeg", "image/png"},
     *     maxSize="1M",
     *     maxWidth="960",
     *     maxHeight="720"
     * )
     */
    protected $articleImage;

    /**
     * @var Image|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Image", cascade={"all"}, orphanRemoval=true)
     *
     * @AssertImageObject(
     *     mimeTypes={"image/jpeg", "image/png"},
     *     maxSize="1M",
     *     maxWidth="960",
     *     maxHeight="720"
     * )
     */
    protected $listImage;

    public function __construct(
        string $title = null,
        string $description = null,
        string $content = null,
        string $youtubeId = null,
        \DateTime $youtubeDuration = null,
        string $shareTwitterText = null,
        string $shareFacebookText = null,
        string $shareEmailSubject = null,
        string $shareEmailBody = null
    ) {
        $this->title = $title;
        $this->description = $description;
        $this->chapters = new ArrayCollection();
        $this->content = $content;
        $this->youtubeId = $youtubeId;
        $this->youtubeDuration = $youtubeDuration ?? MutableDate::create();
        $this->shareTwitterText = $shareTwitterText;
        $this->shareFacebookText = $shareFacebookText;
        $this->shareEmailSubject = $shareEmailSubject;
        $this->shareEmailBody = $shareEmailBody;
    }

    public function __toString()
    {
        return (string) $this->title;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    /**
     * @return Chapter[]|Collection|iterable
     */
    public function getChapters(): iterable
    {
        return $this->chapters;
    }

    /**
     * @JMS\VirtualProperty
     * @JMS\SerializedName("chapter_count"),
     * @JMS\Groups({"mooc_list"})
     */
    public function countChapters(): int
    {
        return \count($this->chapters);
    }

    public function addChapter(Chapter $chapter): void
    {
        if (!$this->chapters->contains($chapter)) {
            $chapter->setMooc($this);
            $this->chapters->add($chapter);
        }
    }

    public function removeChapter(Chapter $chapter): void
    {
        $chapter->detachMooc();
        $this->chapters->removeElement($chapter);
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getYoutubeId(): ?string
    {
        return $this->youtubeId;
    }

    public function setYoutubeId(?string $youtubeId): void
    {
        $this->youtubeId = $youtubeId;
    }

    public function hasYoutubeThumbnail(): bool
    {
        return null !== $this->youtubeId;
    }

    public function getYoutubeThumbnail(): ?string
    {
        return $this->youtubeId ? sprintf('https://img.youtube.com/vi/%s/0.jpg', $this->youtubeId) : null;
    }

    public function getYoutubeDuration(): ?\DateTime
    {
        return $this->youtubeDuration;
    }

    public function setYoutubeDuration(?\DateTime $youtubeDuration): void
    {
        $this->youtubeDuration = $youtubeDuration;
    }

    public function getShareTwitterText(): ?string
    {
        return $this->shareTwitterText;
    }

    public function setShareTwitterText(string $shareTwitterText): void
    {
        $this->shareTwitterText = $shareTwitterText;
    }

    public function getShareFacebookText(): ?string
    {
        return $this->shareFacebookText;
    }

    public function setShareFacebookText(string $shareFacebookText): void
    {
        $this->shareFacebookText = $shareFacebookText;
    }

    public function getShareEmailSubject(): ?string
    {
        return $this->shareEmailSubject;
    }

    public function setShareEmailSubject(string $shareEmailSubject): void
    {
        $this->shareEmailSubject = $shareEmailSubject;
    }

    public function getShareEmailBody(): ?string
    {
        return $this->shareEmailBody;
    }

    public function setShareEmailBody(string $shareEmailBody): void
    {
        $this->shareEmailBody = $shareEmailBody;
    }

    public function getArticleImage(): ?Image
    {
        return $this->articleImage;
    }

    public function setArticleImage(?Image $articleImage): void
    {
        $this->articleImage = $articleImage;
    }

    public function getListImage(): ?Image
    {
        return $this->listImage;
    }

    public function setListImage(?Image $listImage): void
    {
        $this->listImage = $listImage;
    }
}
