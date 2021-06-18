<?php

namespace Tests\App\Validator;

use App\Jecoute\GenderEnum;
use App\Membership\MembershipRequest;
use App\Validator\CustomGender;
use App\Validator\CustomGenderValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class CustomGenderValidatorTest extends ConstraintValidatorTestCase
{
    /**
     * @dataProvider getGender
     */
    public function testValidation(string $gender, ?string $customGender = null, int $violation = 0): void
    {
        $member = new MembershipRequest();
        $member->gender = $gender;
        $member->customGender = $customGender;

        $this->validator->validate($member, new CustomGender());

        $this->assertSame(
            $violation,
            \count($this->context->getViolations()),
            sprintf('%u violation expected. Got gender "%s" for customGender "%s"', $violation, $gender, (string) $customGender)
        );
    }

    public function getGender(): \Iterator
    {
        yield [GenderEnum::MALE, null, 0];
        yield [GenderEnum::FEMALE, null, 0];
        yield [GenderEnum::OTHER, null, 1];
        yield [GenderEnum::MALE, 'label custom gender', 1];
        yield [GenderEnum::FEMALE, 'label custom gender', 1];
        yield [GenderEnum::OTHER, 'label custom gender', 0];
    }

    protected function createValidator(): CustomGenderValidator
    {
        return new CustomGenderValidator();
    }
}
