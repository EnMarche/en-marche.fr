<?php

namespace App\Entity\BoardMember;

use App\Entity\Adherent;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="board_member")
 * @ORM\Entity(repositoryClass="App\Repository\BoardMember\BoardMemberRepository")
 */
class BoardMember
{
    public const AREA_FRANCE_METROPOLITAN = 'metropolitan';
    public const AREA_OVERSEAS_FRANCE = 'overseas';
    public const AREA_ABROAD = 'abroad';

    public const AREAS_CHOICES = [
        'board_member.area.metropolitan' => self::AREA_FRANCE_METROPOLITAN,
        'board_member.area.overseas' => self::AREA_OVERSEAS_FRANCE,
        'board_member.area.abroad' => self::AREA_ABROAD,
    ];

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Adherent", inversedBy="boardMember")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $adherent;

    /**
     * @var string
     *
     * @ORM\Column(length=50)
     *
     * @Assert\NotBlank(message="board_member.area.invalid_choice", groups={"elections"})
     * @Assert\Choice(
     *     callback={"App\Entity\BoardMember\BoardMember", "getAreas"},
     *     message="board_member.area.invalid_choice",
     *     strict=true
     * )
     */
    private $area;

    /**
     * @var Role[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\BoardMember\Role", inversedBy="boardMembers", cascade={"persist"})
     * @ORM\JoinTable(
     *     name="board_member_roles",
     *     joinColumns={
     *         @ORM\JoinColumn(name="board_member_id", referencedColumnName="id", onDelete="CASCADE")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="role_id", referencedColumnName="id", onDelete="CASCADE")
     *     }
     * )
     * @ORM\OrderBy({"name": "ASC"})
     * @Assert\NotNull
     */
    private $roles;

    /**
     * @var BoardMember[]|Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\BoardMember\BoardMember", inversedBy="owners", cascade={"persist"})
     * @ORM\JoinTable(
     *     name="saved_board_members",
     *     joinColumns={
     *         @ORM\JoinColumn(name="board_member_owner_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="board_member_saved_id", referencedColumnName="id")
     *     }
     * )
     */
    private $savedMembers;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\BoardMember\BoardMember", mappedBy="savedMembers")
     *
     * @var BoardMember[]
     */
    private $owners;

    public function __construct()
    {
        $this->savedMembers = new ArrayCollection();
        $this->roles = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAdherent(): ?Adherent
    {
        return $this->adherent;
    }

    public function setAdherent(Adherent $adherent = null)
    {
        $this->adherent = $adherent;
    }

    public function getArea(): string
    {
        return $this->area;
    }

    public function setArea(string $area)
    {
        $this->area = $area;
    }

    public function addRole(Role $role): void
    {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }
    }

    public function replaceRole(Role $actual, Role $new): void
    {
        $this->removeRole($actual);
        $this->addRole($new);
    }

    public function removeRole(Role $role): void
    {
        if ($this->roles->contains($role)) {
            $this->roles->removeElement($role);
        }
    }

    /**
     * @return Role[]|Collection|iterable
     */
    public function getRoles(): iterable
    {
        return $this->roles;
    }

    public function setRoles(iterable $roles): void
    {
        $this->roles = $roles;
    }

    public static function getAreas(): array
    {
        return [
            self::AREA_FRANCE_METROPOLITAN,
            self::AREA_OVERSEAS_FRANCE,
            self::AREA_ABROAD,
        ];
    }

    /**
     * @return BoardMember[]|Collection|iterable
     */
    public function getSavedMembers(): iterable
    {
        return $this->savedMembers;
    }

    public function setSavedMembers(ArrayCollection $savedMembers)
    {
        $this->savedMembers = $savedMembers;
    }

    public function addSavedBoardMember(self $boardMember): void
    {
        if (!$this->savedMembers->contains($boardMember)) {
            $this->savedMembers->add($boardMember);
        }
    }

    public function removeSavedBoardMember(self $boardMember): void
    {
        if ($this->savedMembers->contains($boardMember)) {
            $this->savedMembers->removeElement($boardMember);
        }
    }

    public function hasSavedBoardMember(self $boardMember): bool
    {
        return $this->savedMembers->contains($boardMember);
    }

    public function revoke(): void
    {
        $this->adherent = null;
    }
}
