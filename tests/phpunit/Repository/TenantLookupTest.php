<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Tests\Repository;

use Lle\ConfigBundle\Contracts\ConfigInterface;
use Lle\ConfigBundle\Tests\Fixtures\Config;
use Lle\ConfigBundle\Tests\Support\DoctrineTestCase;

/**
 * Without a tenant, only the global row (tenantId NULL) may be read or written.
 */
class TenantLookupTest extends DoctrineTestCase
{
    private const GROUP = 'Mail';
    private const LABEL = 'host';
    private const TENANT_ID = 4;
    private const TENANT_HOST = 'smtp.tenant.example';
    private const GLOBAL_HOST = 'smtp.global.example';
    private const DEFAULT_HOST = 'localhost';

    protected function setUp(): void
    {
        parent::setUp();

        // tenant row first: an unfiltered lookup would find it before the global one
        $this->saveConfig(Config::create(self::GROUP, self::LABEL, self::TENANT_ID)->setValueString(self::TENANT_HOST));
        $this->saveConfig(Config::create(self::GROUP, self::LABEL)->setValueString(self::GLOBAL_HOST));
        $this->entityManager->clear();
    }

    public function testReadingWithoutTenantReturnsTheGlobalValue(): void
    {
        self::assertSame(self::GLOBAL_HOST, $this->repository->getString(self::GROUP, self::LABEL, self::DEFAULT_HOST));
    }

    public function testReadingWithATenantReturnsTheTenantValue(): void
    {
        self::assertSame(self::TENANT_HOST, $this->repository->getString(self::GROUP, self::LABEL, self::DEFAULT_HOST, self::TENANT_ID));
    }

    public function testInitWithoutTenantReturnsTheGlobalValue(): void
    {
        self::assertSame(self::GLOBAL_HOST, $this->repository->initString(self::GROUP, self::LABEL, self::DEFAULT_HOST));
    }

    public function testWritingWithoutTenantLeavesTheTenantValueUntouched(): void
    {
        $this->repository->setString(self::GROUP, self::LABEL, 'smtp.new.example');
        $this->entityManager->clear();

        self::assertSame(self::TENANT_HOST, $this->findStoredValue(self::TENANT_ID));
        self::assertSame('smtp.new.example', $this->findStoredValue(null));
    }

    public function testMissingGlobalKeyIsCreatedEvenWhenATenantHasIt(): void
    {
        $this->saveConfig(Config::create(self::GROUP, 'port', self::TENANT_ID)->setValueType(ConfigInterface::INT)->setValueInt(2525));
        $this->entityManager->clear();

        self::assertSame(587, $this->repository->getInt(self::GROUP, 'port', 587));
        self::assertSame(2, $this->countStoredConfigs(self::GROUP, 'port'));
    }

    public function testBooleanReadingWithoutTenantReturnsTheGlobalValue(): void
    {
        $this->saveConfig(Config::create(self::GROUP, 'enabled', self::TENANT_ID)->setValueType(ConfigInterface::BOOL)->setValueBool(true));
        $this->saveConfig(Config::create(self::GROUP, 'enabled')->setValueType(ConfigInterface::BOOL)->setValueBool(false));
        $this->entityManager->clear();

        self::assertFalse($this->repository->getBool(self::GROUP, 'enabled', true));
    }

    private function findStoredValue(?int $tenantId): ?string
    {
        $config = $this->repository->findOneBy(['group' => self::GROUP, 'label' => self::LABEL, 'tenantId' => $tenantId]);

        return $config?->getValueString();
    }
}
