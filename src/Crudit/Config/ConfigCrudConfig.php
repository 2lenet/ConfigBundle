<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Crudit\Config;

use Lle\ConfigBundle\Crudit\Datasource\ConfigDatasource;
use Lle\CruditBundle\Contracts\CrudConfigInterface;
use Lle\CruditBundle\Crud\AbstractCrudConfig;
use Lle\CruditBundle\Dto\Field\Field;

class ConfigCrudConfig extends AbstractCrudConfig
{
    public function __construct(
        ConfigDatasource $datasource,
    ) {
        $this->datasource = $datasource;
    }

    public function getListActions(): array
    {
        return [];
    }

    public function getItemActions(): array
    {
        return [];
    }

    public function getFields($key): array
    {
        $group = Field::new('group')->setRuptGroup(1);
        $label = Field::new('label');
        $value = Field::new('valueType')->setLabel('field.value');
        // you can return different fields based on the block key
        if ($key == CrudConfigInterface::INDEX || $key == CrudConfigInterface::SHOW) {
            return [
                $group,
                $label,
                $value->setTemplate("@LleConfig/_value.html.twig"),
            ];
        }

        return [];
    }

    public function getRootRoute(): string
    {
        return 'lle_config_crudit_config';
    }
}
