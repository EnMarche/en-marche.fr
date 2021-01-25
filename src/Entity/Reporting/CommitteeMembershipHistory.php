<?php

namespace App\Entity\Reporting;

use App\Entity\Committee;
use App\Entity\CommitteeMembership;
use App\Entity\ReferentTag;
use Cake\Chronos\Chronos;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Table(
 *     name="committees_membership_histories",
 *     indexes={
 *         @ORM\Index(name="committees_membership_histories_adherent_uuid_idx", columns="adherent_uuid"),
 *         @ORM\Index(name="committees_membership_histories_action_idx", columns="action"),
 *         @ORM\Index(name="committees_membership_histories_date_idx", columns="date")
 *     }
 * )
 * @ORM\Entity
 */
class CommitteeMembershipHistory
{
    /**
     * @var int|null
     *
     * @ORM\Id
     * @ORM\Column(type="integer", options={"unsigned": true})
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var Committee
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Committee", cascade={"persist"})
     */
    private $committee;

    /**
     * Only UUID is used instead of the Entity because we cannot lose this data if one un-subscribes.
     * Otherwise stats would be broken.
     *
     * @var UuidInterface
     *
     * @ORM\Column(type="uuid")
     */
    private $adherentUuid;

    /**
     * @var Collection|ReferentTag[]
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\ReferentTag")
     */
    private $referentTags;

    /**
     * @var string
     *
     * @ORM\Column(length=10)
     */
    private $action;

    /**
     * The privilege given to the member in the committee.
     *
     * Privilege is either HOST or SUPERVISOR or FOLLOWER.
     *
     * @var string
     *
     * @ORM\Column(length=10)
     */
    private $privilege;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime_immutable")
     */
    private $date;

    public function __construct(
        CommitteeMembership $committeeMembership,
        CommitteeMembershipAction $action,
        \DateTimeImmutable $date = null
    ) {
        $this->adherentUuid = $committeeMembership->getAdherentUuid();
        $this->committee = $committeeMembership->getCommittee();
        $this->referentTags = $this->committee->getReferentTags();
        $this->privilege = $committeeMembership->getPrivilege();
        $this->date = $date ?: new Chronos();
        $this->action = $action->getValue();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommittee(): Committee
    {
        return $this->committee;
    }

    public function getAdherentUuid(): UuidInterface
    {
        return $this->adherentUuid;
    }

    /**
     * @return Collection|ReferentTag[]
     */
    public function getReferentTags(): Collection
    {
        return $this->referentTags;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getPrivilege(): string
    {
        return $this->privilege;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }
}
