<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Tests\DependencyInjection;

use Lle\ConfigBundle\DependencyInjection\LleConfigExtension;
use Lle\ConfigBundle\Security\ConfigCredentialWarmup;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class LleConfigExtensionTest extends TestCase
{
    private const PARAMETER_GROUP_ROLES = 'lle_config.group_roles';

    public function testGroupRolesAreDisabledByDefault(): void
    {
        $container = $this->load([]);

        self::assertFalse($container->getParameter(self::PARAMETER_GROUP_ROLES));
    }

    public function testGroupRolesCanBeEnabled(): void
    {
        $container = $this->load([['group_roles' => true]]);

        self::assertTrue($container->getParameter(self::PARAMETER_GROUP_ROLES));
    }

    public function testCredentialWarmupIsRegisteredWhenTheCredentialBundleIsInstalled(): void
    {
        $container = $this->load([]);

        self::assertTrue($container->hasDefinition(ConfigCredentialWarmup::class));
        self::assertTrue($container->getDefinition(ConfigCredentialWarmup::class)->isAutoconfigured());
    }

    /**
     * @param array<array<string, mixed>> $configs
     */
    private function load(array $configs): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $extension = new LleConfigExtension();
        $extension->load($configs, $container);

        return $container;
    }
}
