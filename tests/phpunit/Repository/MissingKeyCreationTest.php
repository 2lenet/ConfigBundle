<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Tests\Repository;

use Lle\ConfigBundle\Tests\Fixtures\Config;
use Lle\ConfigBundle\Tests\Support\DoctrineTestCase;

/**
 * Reading a missing key creates it, without flushing the work the caller has in progress.
 */
class MissingKeyCreationTest extends DoctrineTestCase
{
    private const GROUP = 'Calendar';
    private const MISSING_LABEL = 'showStats';
    private const PENDING_GROUP = 'Caller';
    private const PENDING_LABEL = 'notFlushedYet';

    public function testReadingAMissingKeyDoesNotFlushThePendingWorkOfTheCaller(): void
    {
        $pending = Config::create(self::PENDING_GROUP, self::PENDING_LABEL);
        $this->entityManager->persist($pending);

        self::assertTrue($this->repository->getBool(self::GROUP, self::MISSING_LABEL, true));

        self::assertSame(1, $this->countStoredConfigs(self::GROUP, self::MISSING_LABEL));
        self::assertSame(0, $this->countStoredConfigs(self::PENDING_GROUP, self::PENDING_LABEL));
        self::assertTrue($this->entityManager->getUnitOfWork()->isScheduledForInsert($pending));
    }

    public function testInitialisingAMissingKeyDoesNotFlushThePendingWorkOfTheCaller(): void
    {
        $pending = Config::create(self::PENDING_GROUP, self::PENDING_LABEL);
        $this->entityManager->persist($pending);

        self::assertSame(42, $this->repository->initInt(self::GROUP, 'rows', 42));

        self::assertSame(1, $this->countStoredConfigs(self::GROUP, 'rows'));
        self::assertSame(0, $this->countStoredConfigs(self::PENDING_GROUP, self::PENDING_LABEL));
    }

    public function testCreatedKeyIsStoredWithItsTypeTenantAndDefault(): void
    {
        self::assertSame(1.5, $this->repository->getFloat(self::GROUP, 'ratio', 1.5, 7));
        $this->entityManager->clear();

        $stored = $this->repository->findOneBy(['group' => self::GROUP, 'label' => 'ratio', 'tenantId' => 7]);

        self::assertNotNull($stored);
        self::assertSame('Float', $stored->getValueType());
        self::assertSame(1.5, $stored->getValueFloat());
        self::assertSame(0, $stored->getTri());
    }

    public function testCreatedKeyIsReturnedFromTheCacheOnTheNextReading(): void
    {
        $this->repository->getString(self::GROUP, 'title', 'Planning');
        $this->entityManager->getConnection()->executeStatement(
            'UPDATE ' . self::CONFIG_TABLE . ' SET value_string = ? WHERE label = ?',
            ['Changed in database', 'title'],
        );

        self::assertSame('Planning', $this->repository->getString(self::GROUP, 'title', 'Planning'));
    }

    public function testWritingStillSavesTheValue(): void
    {
        $this->repository->setBool(self::GROUP, self::MISSING_LABEL, false);
        $this->entityManager->clear();

        self::assertFalse($this->repository->getBool(self::GROUP, self::MISSING_LABEL, true));
    }
}
