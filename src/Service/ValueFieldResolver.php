<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Service;

use Lle\ConfigBundle\Contracts\ConfigInterface;

/**
 * Field of the configuration entity holding the value, for each value type.
 */
class ValueFieldResolver
{
    public const VALUE_FIELDS = [
        ConfigInterface::BOOL => 'valueBool',
        ConfigInterface::STRING => 'valueString',
        ConfigInterface::PASSWORD => 'valueString',
        ConfigInterface::TEXT => 'valueText',
        ConfigInterface::INT => 'valueInt',
        ConfigInterface::FLOAT => 'valueFloat',
    ];

    public function getFieldFor(?string $valueType): ?string
    {
        return $valueType === null ? null : self::VALUE_FIELDS[$valueType] ?? null;
    }

    /**
     * @return string[] every value field, once
     */
    public function getValueFields(): array
    {
        return array_values(array_unique(self::VALUE_FIELDS));
    }
}
