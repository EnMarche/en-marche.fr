<?php

namespace Tests\App\Validator;

use App\Donation\DonationRequest;
use App\Donation\PayboxPaymentSubscription;
use App\Repository\TransactionRepository;
use App\Validator\MaxFiscalYearDonation;
use App\Validator\MaxFiscalYearDonationValidator;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class MaxFiscalYearDonationValidatorTest extends ConstraintValidatorTestCase
{
    /**
     * @dataProvider noValidateDonationProvider
     */
    public function testNoValidation(DonationRequest $donationRequest, ?int $value): void
    {
        $this->setObject($donationRequest);

        $this->validator->validate($value, new MaxFiscalYearDonation());

        $this->assertNoViolation();
    }

    public function noValidateDonationProvider(): iterable
    {
        yield 'No validation if no value' => [
            $this->createDonationRequest(),
            null,
        ];
        yield 'No validation if no email' => [
            $this->createDonationRequest(PayboxPaymentSubscription::NONE, null),
            50,
        ];
    }

    /**
     * @dataProvider donationProvider
     */
    public function testValidateWithNoError(
        DonationRequest $donationRequest,
        ?int $value,
        int $maxDonation,
        int $totalCurrentAmount = 0
    ): void {
        $this->setObject($donationRequest);
        $this->validator = $this->createCustomValidatorSuccess($totalCurrentAmount);
        $this->validator->initialize($this->context);

        $this->validator->validate($value, new MaxFiscalYearDonation(['maxDonationInCents' => $maxDonation]));

        $this->assertNoViolation();
    }

    public function donationProvider(): iterable
    {
        yield 'No violation with no subscription 0 total donation' => [
            $this->createDonationRequest(PayboxPaymentSubscription::NONE),
            50,
            7500 * 100,
        ];
        yield 'No violation with no subscription max possible donation' => [
            $this->createDonationRequest(PayboxPaymentSubscription::NONE),
            50,
            7500 * 100,
            7450 * 100,
        ];
        yield 'No violation with subscription 0 total donation' => [
            $this->createDonationRequest(PayboxPaymentSubscription::NONE),
            50,
            7500 * 100,
            0,
        ];
        yield 'No violation with subscription max possible donation' => [
            $this->createDonationRequest(PayboxPaymentSubscription::NONE),
            50,
            7500 * 100,
            7150 * 100,
        ];
    }

    /**
     * @dataProvider donationFailProvider
     */
    public function testValidateWithError(
        array $parameters,
        DonationRequest $donationRequest,
        ?int $value,
        int $maxDonation,
        int $totalCurrentAmount = 0
    ): void {
        $this->setObject($donationRequest);
        $this->validator = $this->createCustomValidatorSuccess($totalCurrentAmount);
        $this->validator->initialize($this->context);

        $this->validator->validate($value, new MaxFiscalYearDonation(['maxDonationInCents' => $maxDonation]));

        $this
            ->buildViolation('donation.max_fiscal_year_donation')
            ->setParameters($parameters)
            ->assertRaised()
        ;
    }

    public function donationFailProvider(): iterable
    {
        yield 'Violation with 0 total donation' => [
            [
                '{{ total_current_amount }}' => 0,
                '{{ max_amount_per_fiscal_year }}' => 7500,
                '{{ max_donation_remaining_possible }}' => 7500,
            ],
            $this->createDonationRequest(PayboxPaymentSubscription::NONE),
            8000,
            7500 * 100,
        ];
        yield 'Violation with max possible donation' => [
            [
                '{{ total_current_amount }}' => 7500,
                '{{ max_amount_per_fiscal_year }}' => 7500,
                '{{ max_donation_remaining_possible }}' => 0,
            ],
            $this->createDonationRequest(PayboxPaymentSubscription::NONE),
            50,
            7500 * 100,
            7500 * 100,
        ];
    }

    protected function createValidator(): MaxFiscalYearDonationValidator
    {
        return $this->createCustomValidatorFail();
    }

    protected function createCustomValidatorFail(): MaxFiscalYearDonationValidator
    {
        $transactionRepository = $this->createMock(TransactionRepository::class);

        $transactionRepository->expects($this->never())
            ->method('getTotalAmountInCentsByEmail')
        ;

        return new MaxFiscalYearDonationValidator(
            $transactionRepository
        );
    }

    protected function createCustomValidatorSuccess(int $totalCurrentAmount = 0): MaxFiscalYearDonationValidator
    {
        $transactionRepository = $this->createMock(TransactionRepository::class);

        $transactionRepository->expects($this->once())
            ->method('getTotalAmountInCentsByEmail')
            ->willReturn($totalCurrentAmount)
        ;

        return new MaxFiscalYearDonationValidator(
            $transactionRepository
        );
    }

    private function createDonationRequest(
        int $duration = PayboxPaymentSubscription::NONE,
        ?string $email = 'test@test.test'
    ): DonationRequest {
        $donationRequest = new DonationRequest(Uuid::uuid4(), '123.0.0.1', 50., $duration);
        $donationRequest->setEmailAddress($email);

        return $donationRequest;
    }
}
