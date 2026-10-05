<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Tests\Crudit;

use Lle\ConfigBundle\Contracts\ConfigInterface;
use Lle\ConfigBundle\Crudit\Datasource\ConfigDatasource;
use Lle\ConfigBundle\Tests\Fixtures\Config;
use Lle\ConfigBundle\Tests\Support\DoctrineTestCase;
use Lle\CruditBundle\Filter\FilterState;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * Saving from the in-place edit of the configuration screen.
 */
class ConfigDatasourceTest extends DoctrineTestCase
{
    private const GROUP = 'opening_hours';
    private const INT_LABEL = 'from';
    private const FLOAT_LABEL = 'ratio';

    public function testZeroIsSavedForAnInteger(): void
    {
        $config = $this->saveConfig(Config::create(self::GROUP, self::INT_LABEL)->setValueType(ConfigInterface::INT)->setValueInt(7));

        self::assertTrue($this->createDatasource()->save($config->setValueInt(0)));
        $this->entityManager->clear();

        self::assertSame(0, $this->findStored(self::INT_LABEL)?->getValueInt());
        self::assertSame(0, $this->repository->getInt(self::GROUP, self::INT_LABEL, 7));
    }

    public function testZeroIsSavedForAFloat(): void
    {
        $config = $this->saveConfig(Config::create(self::GROUP, self::FLOAT_LABEL)->setValueType(ConfigInterface::FLOAT)->setValueFloat(1.5));

        self::assertTrue($this->createDatasource()->save($config->setValueFloat(0.0)));
        $this->entityManager->clear();

        self::assertSame(0.0, $this->findStored(self::FLOAT_LABEL)?->getValueFloat());
    }

    public function testZeroIsSavedForAString(): void
    {
        $config = $this->saveConfig(Config::create(self::GROUP, 'prefix')->setValueString('FAC-'));

        self::assertTrue($this->createDatasource()->save($config->setValueString('0')));
        $this->entityManager->clear();

        self::assertSame('0', $this->findStored('prefix')?->getValueString());
    }

    public function testAnEmptyStringIsSavedAsEmpty(): void
    {
        $config = $this->saveConfig(Config::create(self::GROUP, 'prefix')->setValueString('FAC-'));

        self::assertTrue($this->createDatasource()->save($config->setValueString(null)));
        $this->entityManager->clear();

        self::assertSame('', $this->findStored('prefix')?->getValueString());
    }

    public function testAnEmptyNumberIsStillIgnored(): void
    {
        $config = $this->saveConfig(Config::create(self::GROUP, self::INT_LABEL)->setValueType(ConfigInterface::INT)->setValueInt(7));

        self::assertTrue($this->createDatasource()->save($config->setValueInt(null)));
        // the entity is reloaded: a later flush() of the request must not write the empty value
        self::assertSame(7, $config->getValueInt());
        $this->entityManager->flush();
        $this->entityManager->clear();

        self::assertSame(7, $this->findStored(self::INT_LABEL)?->getValueInt());
    }

    private function createDatasource(): ConfigDatasource
    {
        return new ConfigDatasource(
            $this->entityManager,
            $this->createMock(FilterState::class),
            $this->cacheManager,
            new ParameterBag(['lle_config.using_tenant' => false]),
        );
    }

    private function findStored(string $label): ?ConfigInterface
    {
        return $this->repository->findOneBy(['group' => self::GROUP, 'label' => $label, 'tenantId' => null]);
    }
}
