<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Crudit\Config;

use Lle\ConfigBundle\Contracts\ConfigInterface;
use Lle\ConfigBundle\Service\ValueFieldResolver;
use Lle\CruditBundle\Brick\ListBrick\ListConfig;
use Lle\CruditBundle\Dto\Field\Field;
use Lle\CruditBundle\Crud\AbstractCrudConfig;
use Lle\CruditBundle\Contracts\CrudConfigInterface;
use Lle\ConfigBundle\Crudit\Datasource\ConfigDatasource;

class ConfigCrudConfig extends AbstractCrudConfig
{
    /** Group sidebar + one group displayed; Crudit appends "/index.html.twig". */
    public const LIST_TEMPLATE = '@LleConfig/crudit/list';

    /** In-place edit route of the value cells (_value.html.twig). */
    public const EDIT_DATA_ROUTE = 'lle_config_crudit_config_editdata';

    public function __construct(
        ConfigDatasource $datasource,
        protected ValueFieldResolver $valueFieldResolver = new ValueFieldResolver(),
    )
    {
        $this->datasource = $datasource;
    }

    /**
     * Fields the editdata route may change (checked server side by Crudit >= 1.21, lle_crudit.eip_strict).
     * The value cells are written by hand in _value.html.twig, so they are declared here: only the value field
     * matching the stored value type is editable; group, label, valueType, tenantId… are refused.
     * Without effect on an older Crudit, which does not call this method.
     *
     * @return Field[]
     */
    public function getEipFields(): array
    {
        $eipFields = [];
        foreach ($this->valueFieldResolver->getValueFields() as $valueField) {
            $eipFields[] = Field::new($valueField)
                ->setEditable(self::EDIT_DATA_ROUTE)
                ->setEditableIf(
                    fn (ConfigInterface $config): bool
                        => $this->valueFieldResolver->getFieldFor($config->getValueType()) === $valueField,
                );
        }

        return $eipFields;
    }

    public function getFields(string $key): array
    {
        $group = Field::new('group')->setRuptGroup(1);
        $label = Field::new('label')->setLabel('field.code');
        $transLabel = Field::new('label')
            ->setTemplate('@LleConfig/_trans_label.html.twig')
            ->setLabel('field.label');
        $value = Field::new('valueType')->setLabel('field.value');

        if ($key == CrudConfigInterface::INDEX || $key == CrudConfigInterface::SHOW) {
            return [
                $group,
                $label,
                $transLabel,
                $value->setTemplate("@LleConfig/_value.html.twig"),
            ];
        }

        return [];
    }

    public function getBrickConfigs(): array
    {
        $brickConfigs = parent::getBrickConfigs();

        foreach ($brickConfigs[CrudConfigInterface::INDEX] as $brickConfig) {
            if ($brickConfig instanceof ListConfig) {
                $brickConfig->setTemplate(self::LIST_TEMPLATE);
            }
        }

        return $brickConfigs;
    }

    public function getNbItems(): int
    {
        return 500;
    }

    public function getListActions(): array
    {
        return [];
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
