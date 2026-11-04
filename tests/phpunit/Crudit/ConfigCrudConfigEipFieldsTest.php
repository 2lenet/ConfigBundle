<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Tests\Crudit;

use Lle\ConfigBundle\Contracts\ConfigInterface;
use Lle\ConfigBundle\Crudit\Config\ConfigCrudConfig;
use Lle\ConfigBundle\Crudit\Datasource\ConfigDatasource;
use Lle\CruditBundle\Dto\Field\Field;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Crudit >= 1.21 (lle_crudit.eip_strict) only lets editdata change the fields returned by getEipFields():
 * the value cells of _value.html.twig are written by hand, so they must be declared here.
 */
class ConfigCrudConfigEipFieldsTest extends TestCase
{
    public function testTheValueFieldsAreEditableInPlaceThroughTheConfigRoute(): void
    {
        $crudConfig = new ConfigCrudConfig($this->createMock(ConfigDatasource::class));

        $eipFields = $crudConfig->getEipFields();

        self::assertSame(
            ['valueBool', 'valueString', 'valueText', 'valueInt', 'valueFloat'],
            array_map(static fn (Field $field): string => $field->getName(), $eipFields),
        );
        foreach ($eipFields as $field) {
            self::assertTrue($field->getEditInPlace());
            self::assertSame(ConfigCrudConfig::EDIT_DATA_ROUTE, $field->getOptions()['edit_route'] ?? null);
        }
    }

    public function testIdentityFieldsAreNotEditableInPlace(): void
    {
        $crudConfig = new ConfigCrudConfig($this->createMock(ConfigDatasource::class));

        $names = array_map(static fn (Field $field): string => $field->getName(), $crudConfig->getEipFields());

        foreach (['group', 'label', 'valueType', 'tenantId', 'tri'] as $identityField) {
            self::assertNotContains($identityField, $names);
        }
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function valueTypeProvider(): iterable
    {
        yield 'bool' => [ConfigInterface::BOOL, 'valueBool'];
        yield 'string' => [ConfigInterface::STRING, 'valueString'];
        yield 'password' => [ConfigInterface::PASSWORD, 'valueString'];
        yield 'text' => [ConfigInterface::TEXT, 'valueText'];
        yield 'int' => [ConfigInterface::INT, 'valueInt'];
        yield 'float' => [ConfigInterface::FLOAT, 'valueFloat'];
    }

    /**
     * Only the field matching the stored value type may be written (no valueString on a Bool configuration).
     */
    #[DataProvider('valueTypeProvider')]
    public function testOnlyTheFieldOfTheValueTypeIsEditable(string $valueType, string $expectedField): void
    {
        $crudConfig = new ConfigCrudConfig($this->createMock(ConfigDatasource::class));
        $config = $this->createMock(ConfigInterface::class);
        $config->method('getValueType')->willReturn($valueType);

        $editableFields = [];
        foreach ($crudConfig->getEipFields() as $field) {
            $editableIf = $field->getEditableIf();
            self::assertNotNull($editableIf);
            if ($editableIf($config)) {
                $editableFields[] = $field->getName();
            }
        }

        self::assertSame([$expectedField], $editableFields);
    }

    public function testNoFieldIsEditableForAnUnknownValueType(): void
    {
        $crudConfig = new ConfigCrudConfig($this->createMock(ConfigDatasource::class));
        $config = $this->createMock(ConfigInterface::class);
        $config->method('getValueType')->willReturn('Unknown');

        $eipFields = $crudConfig->getEipFields();

        self::assertNotEmpty($eipFields);
        foreach ($eipFields as $field) {
            $editableIf = $field->getEditableIf();
            self::assertNotNull($editableIf);
            self::assertFalse($editableIf($config));
        }
    }
}
