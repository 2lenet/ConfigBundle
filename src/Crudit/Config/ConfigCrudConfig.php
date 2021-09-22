<?php

declare(strict_types=1);

namespace App\Crudit\Config;

use Lle\CruditBundle\Dto\Field\Field;
use Lle\CruditBundle\Brick\LinksBrick\LinksConfig;
use Lle\CruditBundle\Brick\ListBrick\ListConfig;
use Lle\CruditBundle\Brick\ShowBrick\ShowConfig;
use Lle\CruditBundle\Brick\FormBrick\FormConfig;
use Lle\CruditBundle\Crud\AbstractCrudConfig;
use Lle\CruditBundle\Contracts\DatasourceInterface;
use Lle\CruditBundle\Dto\Action\ListAction;
use Lle\CruditBundle\Dto\Action\ItemAction;
use Symfony\Component\HttpFoundation\Request;
use Lle\CruditBundle\Contracts\CrudConfigInterface;
use App\Form\ConfigType;
use App\Crudit\Datasource\ConfigDatasource;

class ConfigCrudConfig extends AbstractCrudConfig
{
    public function __construct(
        ConfigDatasource $datasource
    )
    {
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
        $groupe = Field::new('groupe')->setRuptGroup(1);
        $libelle = Field::new('libelle');
        $value = Field::new('valueType')->setLabel('field.value');
        $bool = Field::new('valueBool')->setEditable("app_crudit_config_editdata");
        // you can return different fields based on the block key
        if ($key == CrudConfigInterface::INDEX || $key == CrudConfigInterface::SHOW) {
            return [
                $groupe,
                $libelle,
                //$bool,
                $value,
//                $value->setTemplate("config/_value.html.twig"),
            ];
        }

        return [];
    }

    public function getRootRoute(): string
    {
        return 'app_crudit_config';
    }

}
