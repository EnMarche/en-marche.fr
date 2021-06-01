<?php

namespace App\Entity\Reporting;

use App\Entity\Administrator;
use App\Entity\Committee;
use App\Entity\CommitteeMembership;
use Cake\Chronos\Chronos;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(
 *     name="committee_merge_histories",
 *     indexes={
 *         @ORM\Index(name="committee_merge_histories_source_committee_id_idx", columns="source_committee_id"),
 *         @ORM\Index(name="committee_merge_histories_destination_committee_id_idx", columns="destination_committee_id"),
 *         @ORM\Index(name="committee_merge_histories_date_idx", columns="date")
 *     }
 * )
 * @ORM\Entity
 */
class CommitteeMergeHistory
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
     * @var Committee
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Committee")
     * @ORM\JoinColumn(nullable=false)
     */
    private $sourceCommittee;

    /**
     * @var Committee
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Committee")
     * @ORM\JoinColumn(nullable=false)
     */
    private $destinationCommittee;

    /**
     * @var Administrator|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Administrator")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $mergedBy;

    /**
     * @var \DateTimeImmutable
     *
     * @ORM\Column(type="datetime_immutable")
     */
    private $date;

    /**
     * @var CommitteeMembership[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\CommitteeMembership")
     * @ORM\JoinTable(
     *     name="committee_merge_histories_merged_memberships",
     *     joinColumns={
     *         @ORM\JoinColumn(name="committee_merge_history_id", referencedColumnName="id", onDelete="CASCADE")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="committee_membership_id", referencedColumnName="id", unique=true, onDelete="CASCADE")
     *     }
     * )
     */
    private $mergedMemberships;

    /**
     * @var Administrator|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Administrator")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $revertedBy;

    /**
     * @var \DateTimeImmutable|null
     *
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $revertedAt;

    public function __construct(
        Committee $sourceCommittee,
        Committee $destinationCommittee,
        array $mergedMemberships,
        Administrator $admin,
        \DateTimeImmutable $date = null
    ) {
        $this->sourceCommittee = $sourceCommittee;
        $this->destinationCommittee = $destinationCommittee;
        $this->mergedBy = $admin;
        $this->date = $date ?: new Chronos();
        $this->mergedMemberships = new ArrayCollection($mergedMemberships);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSourceCommittee(): Committee
    {
        return $this->sourceCommittee;
    }

    public function getDestinationCommittee(): Committee
    {
        return $this->destinationCommittee;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function getMergedBy(): Administrator
    {
        return $this->mergedBy;
    }

    public function getRevertedBy(): ?Administrator
    {
        return $this->revertedBy;
    }

    public function getRevertedAt(): ?\DateTimeImmutable
    {
        return $this->revertedAt;
    }

    public function revert(Administrator $administrator): void
    {
        if ($this->isReverted()) {
            throw new \LogicException('CommitteeMergeHistory is already reverted.');
        }

        $this->revertedBy = $administrator;
        $this->revertedAt = new \DateTimeImmutable();
        $this->mergedMemberships = new ArrayCollection();
    }

    public function isReverted(): bool
    {
        return $this->revertedBy || $this->revertedAt;
    }

    /**
     * @return CommitteeMembership[]
     */
    public function getMergedMemberships(): array
    {
        return $this->mergedMemberships->toArray();
    }
}
