<?php

namespace AppBundle\AdherentMessage\Handler;

use AppBundle\Messenger\Message\AsyncMessageInterface;

class AdherentMessageDeleteCommand implements AsyncMessageInterface
{
    private $campaignId;

    public function __construct(string $campaignId)
    {
        $this->campaignId = $campaignId;
    }

    public function getCampaignId(): string
    {
        return $this->campaignId;
    }
}
