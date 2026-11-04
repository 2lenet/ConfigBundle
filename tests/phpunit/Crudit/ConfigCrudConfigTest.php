<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Tests\Crudit;

use Lle\ConfigBundle\Crudit\Config\ConfigCrudConfig;
use Lle\ConfigBundle\Crudit\Datasource\ConfigDatasource;
use Lle\ConfigBundle\Security\ConfigAccessChecker;
use Lle\CruditBundle\Brick\ListBrick\ListConfig;
use Lle\CruditBundle\Contracts\CrudConfigInterface;
use PHPUnit\Framework\TestCase;

class ConfigCrudConfigTest extends TestCase
{
    public function testListUsesTheGroupedConfigurationTemplate(): void
    {
        $crudConfig = $this->createCrudConfig();

        $listConfigs = array_values(array_filter(
            $crudConfig->getBrickConfigs()[CrudConfigInterface::INDEX],
            static fn (object $brickConfig): bool => $brickConfig instanceof ListConfig,
        ));

        self::assertCount(1, $listConfigs);
        self::assertSame(ConfigCrudConfig::LIST_TEMPLATE, $listConfigs[0]->getTemplate());
    }

    public function testShowPageKeepsTheDefaultTemplates(): void
    {
        $crudConfig = $this->createCrudConfig();

        foreach ($crudConfig->getBrickConfigs()[CrudConfigInterface::SHOW] as $brickConfig) {
            self::assertNull($brickConfig->getTemplate());
        }
    }

    private function createCrudConfig(): ConfigCrudConfig
    {
        return new ConfigCrudConfig($this->createStub(ConfigDatasource::class), new ConfigAccessChecker(null, false));
    }
}
