<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Crudit\Config;

use Lle\CruditBundle\Dto\Action\ListAction;
use Lle\CruditBundle\Dto\Field\Field;
use Lle\CruditBundle\Crud\AbstractCrudConfig;
use Lle\CruditBundle\Contracts\CrudConfigInterface;
use Lle\ConfigBundle\Crudit\Datasource\ConfigDatasource;
use Lle\CruditBundle\Dto\Icon;
use Lle\CruditBundle\Dto\Path;

class ConfigCrudConfig extends AbstractCrudConfig
{
    public function __construct(
        ConfigDatasource $datasource
    )
    {
        $this->datasource = $datasource;
    }

    public function getFields(string $key): array
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

    public function getListActions(): array
    {
        return [
            ListAction::new(
                '',
                Path::new('lle_config_crudit_config_refreshcache'),
                Icon::new('sync')
            )->setHideLabel(true)
        ];
    }

    public function getItemActions(): array
    {
        return [];
    }

    public function getRootRoute(): string
    {
        return 'lle_config_crudit_config';
    }
}
