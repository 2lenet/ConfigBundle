<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Tests\Repository;

use Lle\ConfigBundle\Contracts\ConfigInterface;
use Lle\ConfigBundle\Tests\Fixtures\Config;
use Lle\ConfigBundle\Tests\Support\DoctrineTestCase;

/**
 * A password is cached like any other value: the second reading does not query the database.
 */
class PasswordCacheTest extends DoctrineTestCase
{
    private const GROUP = 'Mail';
    private const LABEL = 'password';
    private const STORED_PASSWORD = 'first-secret';

    public function testSecondReadingOfAPasswordComesFromTheCache(): void
    {
        $this->saveConfig(
            Config::create(self::GROUP, self::LABEL)->setValueType(ConfigInterface::PASSWORD)->setValueString(self::STORED_PASSWORD),
        );
        $this->entityManager->clear();

        self::assertSame(self::STORED_PASSWORD, $this->repository->getPassword(self::GROUP, self::LABEL, ''));
        $this->changeStoredPassword('changed-in-database');

        self::assertSame(self::STORED_PASSWORD, $this->repository->getPassword(self::GROUP, self::LABEL, ''));
    }

    public function testWritingAPasswordRefreshesTheCache(): void
    {
        $this->repository->getPassword(self::GROUP, self::LABEL, self::STORED_PASSWORD);

        $this->repository->setPassword(self::GROUP, self::LABEL, 'new-secret');

        self::assertSame('new-secret', $this->repository->getPassword(self::GROUP, self::LABEL, self::STORED_PASSWORD));
    }

    private function changeStoredPassword(string $password): void
    {
        $this->entityManager->getConnection()->executeStatement(
            'UPDATE ' . self::CONFIG_TABLE . ' SET value_string = ? WHERE label = ?',
            [$password, self::LABEL],
        );
        $this->entityManager->clear();
    }
}
