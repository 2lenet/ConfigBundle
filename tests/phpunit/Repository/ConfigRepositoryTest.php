<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Tests\Repository;

use Lle\ConfigBundle\Tests\Fixtures\Config;
use Lle\ConfigBundle\Tests\Support\DoctrineTestCase;

class ConfigRepositoryTest extends DoctrineTestCase
{
    private const GROUP = 'Mail';
    private const LABEL = 'host';
    private const DEFAULT_HOST = 'localhost';

    public function testMissingKeyIsCreatedWithItsDefault(): void
    {
        self::assertSame(self::DEFAULT_HOST, $this->repository->getString(self::GROUP, self::LABEL, self::DEFAULT_HOST));
        self::assertSame(1, $this->countStoredConfigs(self::GROUP, self::LABEL));
    }

    public function testStoredValueIsReturned(): void
    {
        $this->saveConfig(Config::create(self::GROUP, self::LABEL)->setValueString('smtp.example.org'));

        self::assertSame('smtp.example.org', $this->repository->getString(self::GROUP, self::LABEL, self::DEFAULT_HOST));
    }
}
