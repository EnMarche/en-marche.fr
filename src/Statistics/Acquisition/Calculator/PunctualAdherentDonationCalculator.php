<?php

namespace AppBundle\Statistics\Acquisition\Calculator;

use AppBundle\Donation\PayboxPaymentSubscription;
use AppBundle\Entity\Donation;

class PunctualAdherentDonationCalculator extends AbstractDonationCalculator
{
    public function getLabel(): string
    {
        return 'Dons ponctuels par des adherents (total)';
    }

    protected function getDonationStatus(): string
    {
        return Donation::STATUS_FINISHED;
    }

    protected function getDonationDuration(): int
    {
        return PayboxPaymentSubscription::NONE;
    }

    protected function isAdherentOnly(): bool
    {
        return true;
    }
}
