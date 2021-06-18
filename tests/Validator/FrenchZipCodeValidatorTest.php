<?php

namespace Tests\App\Validator;

use App\Address\Address;
use App\Validator\FrenchZipCode;
use App\Validator\FrenchZipCodeValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class FrenchZipCodeValidatorTest extends ConstraintValidatorTestCase
{
    public function testValidateWithMissContext(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $this->setObject(new \stdClass());

        $this->validator->validate('42', new FrenchZipCode());
    }

    /**
     * @dataProvider validZipProvider
     */
    public function testValidateWithNoError(?string $zip, string $country): void
    {
        $this->setAddress($country);

        $this->validator->validate($zip, new FrenchZipCode());

        $this->assertNoViolation();
    }

    public function testValidateWithError(): void
    {
        $this->setAddress(Address::FRANCE);

        $this->validator->validate('7500', new FrenchZipCode());

        $this
            ->buildViolation('common.postal_code.invalid')
            ->assertRaised()
        ;
    }

    public function validZipProvider(): \Generator
    {
        yield 'No validation on null' => [
            null,
            Address::FRANCE,
        ];
        yield 'French address' => [
            '75001',
            Address::FRANCE,
        ];
        yield 'Foreign address' => [
            '75001-65433',
            'EN',
        ];
    }

    protected function createValidator(): FrenchZipCodeValidator
    {
        return new FrenchZipCodeValidator();
    }

    protected function setAddress(string $country): void
    {
        $address = new Address();
        $address->setCountry($country);
        $this->setObject($address);
    }
}
