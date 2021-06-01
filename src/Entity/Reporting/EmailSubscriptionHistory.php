<?php

namespace App\Entity\Reporting;

use App\Entity\Adherent;
use App\Entity\ReferentTag;
use App\Entity\SubscriptionType;
use Cake\Chronos\Chronos;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Webmozart\Assert\Assert;

/**
 * @ORM\Table(
 *     name="adherent_email_subscription_histories",
 *     indexes={
 *         @ORM\Index(name="adherent_email_subscription_histories_adherent_uuid_idx", columns="adherent_uuid"),
 *         @ORM\Index(name="adherent_email_subscription_histories_adherent_action_idx", columns="action"),
 *         @ORM\Index(name="adherent_email_subscription_histories_adherent_date_idx", columns="date")
 *     }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\EmailSubscriptionHistoryRepository")
 */
class EmailSubscriptionHistory
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Only UUID is used instead of the Entity because we cannot lose this data if one unsubscribes.
     * Otherwise stats would be broken.
     *
     * @var UuidInterface
     *
     * @ORM\Column(type="uuid")
     */
    private $adherentUuid;

    /**
     * @var SubscriptionType
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\SubscriptionType")
     * @ORM\JoinColumn(nullable=false)
     */
    private $subscriptionType;

    /**
     * @var Collection|ReferentTag[]
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\ReferentTag")
     * @ORM\JoinTable(name="adherent_email_subscription_history_referent_tag")
     */
    private $referentTags;

    /**
     * @var string
     *
     * @ORM\Column(length=32)
     */
    private $action;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime_immutable")
     */
    private $date;

    public function __construct(
        Adherent $adherent,
        SubscriptionType $subscriptionType,
        array $referentTags,
        EmailSubscriptionHistoryAction $action,
        \DateTimeImmutable $date = null
    ) {
        $this->adherentUuid = $adherent->getUuid();
        $this->subscriptionType = $subscriptionType;
        $this->action = $action->getValue();
        $this->date = $date ?: new Chronos();

        $this->referentTags = new ArrayCollection();
        foreach ($referentTags as $tag) {
            Assert::isInstanceOf($tag, ReferentTag::class);

            $this->referentTags->add($tag);
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAdherentUuid(): UuidInterface
    {
        return $this->adherentUuid;
    }

    public function getSubscriptionType(): string
    {
        return $this->subscriptionType;
    }

    /**
     * @return Collection|ReferentTag[]
     */
    public function getReferentTags(): Collection
    {
        return $this->referentTags;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function getAction(): string
    {
        return $this->action;
    }
}
