<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Tests\Crudit;

use Lle\ConfigBundle\Crudit\Config\ConfigCrudConfig;
use Lle\ConfigBundle\Crudit\Datasource\ConfigDatasource;
use Lle\CruditBundle\Brick\ListBrick\ListConfig;
use Lle\CruditBundle\Contracts\CrudConfigInterface;
use PHPUnit\Framework\TestCase;

class ConfigCrudConfigTest extends TestCase
{
    public function testListUsesTheGroupedConfigurationTemplate(): void
    {
        $crudConfig = new ConfigCrudConfig($this->createMock(ConfigDatasource::class));

        $listConfigs = array_values(array_filter(
            $crudConfig->getBrickConfigs()[CrudConfigInterface::INDEX],
            static fn (object $brickConfig): bool => $brickConfig instanceof ListConfig,
        ));

        self::assertCount(1, $listConfigs);
        self::assertSame(ConfigCrudConfig::LIST_TEMPLATE, $listConfigs[0]->getTemplate());
    }

    public function testShowPageKeepsTheDefaultTemplates(): void
    {
        $crudConfig = new ConfigCrudConfig($this->createMock(ConfigDatasource::class));

        foreach ($crudConfig->getBrickConfigs()[CrudConfigInterface::SHOW] as $brickConfig) {
            self::assertNull($brickConfig->getTemplate());
        }
    }
}
