<?php

namespace Tests\App\Repository;

use App\Repository\NewsletterSubscriptionRepository;
use Tests\App\AbstractKernelTestCase;

/**
 * @group functional
 */
class NewsletterSubscriptionRepositoryTest extends AbstractKernelTestCase
{
    /** @var NewsletterSubscriptionRepository */
    private $repository;

    public function testFindAllManagedBy()
    {
        $referent = $this->getAdherentRepository()->loadUserByUsername('referent@en-marche-dev.fr');

        $managedByReferent = $this->repository->findAllManagedBy($referent);

        $this->assertCount(3, $managedByReferent, 'Referent should manage 3 newsletter subscribers in his area.');
        $this->assertSame('abc@en-marche-dev.fr', $managedByReferent[0]->getEmail());
        $this->assertSame('def@en-marche-dev.fr', $managedByReferent[1]->getEmail());
        $this->assertSame('ghi@en-marche-dev.fr', $managedByReferent[2]->getEmail());
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->getNewsletterSubscriptionRepository();
    }

    protected function tearDown(): void
    {
        $this->repository = null;

        parent::tearDown();
    }
}
