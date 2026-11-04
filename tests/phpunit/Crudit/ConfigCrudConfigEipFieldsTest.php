<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Tests\Crudit;

use Lle\ConfigBundle\Contracts\ConfigInterface;
use Lle\ConfigBundle\Crudit\Config\ConfigCrudConfig;
use Lle\ConfigBundle\Crudit\Datasource\ConfigDatasource;
use Lle\ConfigBundle\Security\ConfigAccessChecker;
use Lle\ConfigBundle\Tests\Fixtures\Config;
use Lle\CruditBundle\Dto\Field\Field;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Crudit >= 1.21 (lle_crudit.eip_strict) only lets editdata change the fields returned by getEipFields():
 * the value cells of _value.html.twig are written by hand, so they must be declared here.
 */
class ConfigCrudConfigEipFieldsTest extends TestCase
{
    private const GROUP = 'Saferpay';
    private const ROLE_SHOW = 'ROLE_CONFIG_ACTION_SHOW_GROUP_SAFERPAY';
    private const ROLE_EDIT = 'ROLE_CONFIG_ACTION_EDIT_GROUP_SAFERPAY';
    private const ROLE_SECRET = 'ROLE_CONFIG_ACTION_SECRET_GROUP_SAFERPAY';

    public function testTheValueFieldsAreEditableInPlaceThroughTheConfigRoute(): void
    {
        $crudConfig = $this->createCrudConfig(false, []);

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
        $crudConfig = $this->createCrudConfig(false, []);

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
        $crudConfig = $this->createCrudConfig(false, []);

        self::assertSame([$expectedField], $this->findEditableFields($crudConfig, $this->createConfig($valueType)));
    }

    public function testNoFieldIsEditableForAnUnknownValueType(): void
    {
        $crudConfig = $this->createCrudConfig(false, []);

        self::assertSame([], $this->findEditableFields($crudConfig, $this->createConfig('Unknown')));
    }

    public function testNoFieldIsEditableWithoutEditRole(): void
    {
        $crudConfig = $this->createCrudConfig(true, [self::ROLE_SHOW]);

        self::assertSame([], $this->findEditableFields($crudConfig, $this->createConfig(ConfigInterface::STRING)));
    }

    public function testTheValueIsEditableWithEditRole(): void
    {
        $crudConfig = $this->createCrudConfig(true, [self::ROLE_EDIT]);

        self::assertSame(['valueString'], $this->findEditableFields($crudConfig, $this->createConfig(ConfigInterface::STRING)));
    }

    public function testASecretIsNotEditableWithoutSecretRole(): void
    {
        $crudConfig = $this->createCrudConfig(true, [self::ROLE_EDIT]);

        self::assertSame([], $this->findEditableFields($crudConfig, $this->createConfig(ConfigInterface::PASSWORD)));
    }

    public function testASecretIsEditableWithEditAndSecretRoles(): void
    {
        $crudConfig = $this->createCrudConfig(true, [self::ROLE_EDIT, self::ROLE_SECRET]);

        self::assertSame(['valueString'], $this->findEditableFields($crudConfig, $this->createConfig(ConfigInterface::PASSWORD)));
    }

    /**
     * @return string[] names of the fields whose editableIf accepts the configuration
     */
    private function findEditableFields(ConfigCrudConfig $crudConfig, ConfigInterface $config): array
    {
        $eipFields = $crudConfig->getEipFields();
        self::assertNotEmpty($eipFields);

        $editableFields = [];
        foreach ($eipFields as $field) {
            $editableIf = $field->getEditableIf();
            self::assertNotNull($editableIf);
            if ($editableIf($config)) {
                $editableFields[] = $field->getName();
            }
        }

        return $editableFields;
    }

    /**
     * @param string[] $grantedRoles
     */
    private function createCrudConfig(bool $groupRoles, array $grantedRoles): ConfigCrudConfig
    {
        $authorizationChecker = $this->createStub(AuthorizationCheckerInterface::class);
        $authorizationChecker
            ->method('isGranted')
            ->willReturnCallback(static fn (mixed $attribute): bool => in_array($attribute, $grantedRoles, true));

        return new ConfigCrudConfig(
            $this->createStub(ConfigDatasource::class),
            new ConfigAccessChecker($authorizationChecker, $groupRoles),
        );
    }

    private function createConfig(string $valueType): Config
    {
        return Config::create(self::GROUP, 'login')->setValueType($valueType);
    }
}
