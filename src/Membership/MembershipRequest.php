<?php

namespace AppBundle\Membership;

use AppBundle\Address\Address;
use AppBundle\Entity\Adherent;
use AppBundle\Validator\Recaptcha as AssertRecaptcha;
use AppBundle\Validator\UniqueMembership as AssertUniqueMembership;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberUtil;
use Misd\PhoneNumberBundle\Validator\Constraints\PhoneNumber as AssertPhoneNumber;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @AssertUniqueMembership(groups={"Registration", "Update"})
 */
class MembershipRequest implements MembershipInterface
{
    /**
     * @Assert\NotBlank(message="common.gender.not_blank", groups={"Update"})
     * @Assert\Choice(
     *   callback = {"AppBundle\ValueObject\Genders", "all"},
     *   message="common.gender.invalid_choice",
     *   strict=true,
     *   groups={"Update"}
     * )
     */
    public $gender;

    /**
     * @Assert\NotBlank(message="common.first_name.not_blank", groups={"Registration", "Update"})
     * @Assert\Length(
     *   min=2,
     *   max=50,
     *   minMessage="common.first_name.min_length",
     *   maxMessage="common.first_name.max_length",
     *   groups={"Registration", "Update"}
     * )
     */
    public $firstName;

    /**
     * @Assert\NotBlank(message="common.first_name.not_blank", groups={"Registration", "Update"})
     * @Assert\Length(
     *   min=2,
     *   max=50,
     *   minMessage="common.last_name.min_length",
     *   maxMessage="common.last_name.max_length",
     *   groups={"Registration", "Update"}
     * )
     */
    public $lastName;

    /**
     * @var Address
     *
     * @Assert\Valid
     */
    private $address;

    /**
     * @Assert\Choice(
     *   callback = {"AppBundle\Membership\ActivityPositions", "all"},
     *   message="adherent.activity_position.invalid_choice",
     *   strict=true,
     *   groups={"Update"}
     * )
     */
    public $position;

    /**
     * @Assert\NotBlank(groups="Registration")
     * @Assert\Length(min=8, minMessage="adherent.plain_password.min_length", groups={"Registration"})
     */
    public $password;

    /**
     * @Assert\IsTrue(message="common.conditions.not_accepted", groups={"Conditions"})
     */
    public $conditions;

    public $comMobile = false;

    public $comEmail = false;

    /**
     * @Assert\NotBlank(message="common.recaptcha.invalid_message", groups={"Registration"})
     * @AssertRecaptcha(groups={"Registration"})
     */
    public $recaptcha;

    /**
     * @Assert\NotBlank(message="common.email.not_blank", groups={"Registration", "Update"})
     * @Assert\Email(message="common.email.invalid", groups={"Registration", "Update"})
     * @Assert\Length(max=255, maxMessage="common.email.max_length", groups={"Registration", "Update"})
     */
    private $emailAddress;

    /**
     * @AssertPhoneNumber(defaultRegion="FR", groups={"Update"})
     * @Assert\NotBlank(message="common.phone_number.required", groups={"Update"})
     */
    private $phone;

    /**
     * @Assert\NotBlank(message="adherent.birthdate.not_blank", groups={"Update"})
     * @Assert\Range(max="-15 years", maxMessage="adherent.birthdate.minimum_required_age", groups={"Update"})
     */
    private $birthdate;

    public function __construct()
    {
        $this->address = new Address();
    }

    public static function createWithCaptcha(?string $countryIso, string $recaptchaAnswer = null): self
    {
        $dto = new self();
        $dto->recaptcha = $recaptchaAnswer;

        if ($countryIso) {
            $dto->address->setCountry($countryIso);
        }

        return $dto;
    }

    public static function createFromAdherent(Adherent $adherent, PhoneNumberUtil $phoneNumberUtil): self
    {
        $dto = new self();
        $dto->gender = $adherent->getGender();
        $dto->firstName = $adherent->getFirstName();
        $dto->lastName = $adherent->getLastName();
        $dto->birthdate = $adherent->getBirthdate();
        $dto->position = $adherent->getPosition();
        $dto->address = Address::createFromAddress($adherent->getPostAddress());
        $dto->phone = $adherent->getPhone();
        $dto->comMobile = $adherent->getComMobile();
        $dto->emailAddress = $adherent->getEmailAddress();

        if (!$dto->phone) {
            $countryCode = $phoneNumberUtil->getCountryCodeForRegion($dto->address->getCountry());
            $countryCode = $countryCode ?: 33;
            $dto->phone = new PhoneNumber();
            $dto->phone->setCountryCode($countryCode);
        }

        return $dto;
    }

    public function setAddress(Address $address = null): void
    {
        $this->address = $address;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setEmailAddress(string $emailAddress): void
    {
        $this->emailAddress = mb_strtolower($emailAddress);
    }

    public function getEmailAddress(): string
    {
        return $this->emailAddress ?: '';
    }

    public function setPhone(PhoneNumber $phone = null): void
    {
        $this->phone = $phone;
    }

    public function getPhone(): ?PhoneNumber
    {
        return $this->phone;
    }

    public function setBirthdate(\DateTime $birthdate = null): void
    {
        $this->birthdate = $birthdate;
    }

    public function getBirthdate(): ?\DateTime
    {
        return $this->birthdate;
    }
}
