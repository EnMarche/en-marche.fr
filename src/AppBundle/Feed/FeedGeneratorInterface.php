<?php

namespace AppBundle\Feed;

use AppBundle\Feed\Exception\FeedGeneratorException;
use Suin\RSSWriter\FeedInterface;

interface FeedGeneratorInterface
{
    /**
     * Build and populate the feed content from various data sources.
     *
     * @param mixed $data
     *
     * @return FeedInterface
     *
     * @throws FeedGeneratorException when any error occurs
     */
    public function buildFeed($data): FeedInterface;
}
