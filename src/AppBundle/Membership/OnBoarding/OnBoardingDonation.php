<?php

namespace AppBundle\Membership\OnBoarding;

use AppBundle\Donation\DonationRequest;
use AppBundle\Membership\MembershipOnBoardingInterface;

/**
 * A simple instance to store a donation request while on boarding.
 */
final class OnBoardingDonation implements MembershipOnBoardingInterface
{
    private $donationRequest;

    public function __construct(DonationRequest $donationRequest)
    {
        $this->donationRequest = $donationRequest;
    }

    public function getDonationRequest(): DonationRequest
    {
        return $this->donationRequest;
    }
}
