<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Tests\Twig;

use Lle\ConfigBundle\Service\GroupNavigation;
use Lle\ConfigBundle\Service\LabelFormatter;
use Lle\ConfigBundle\Twig\GroupNavigationExtension;
use PHPUnit\Framework\TestCase;
use Twig\TwigFilter;
use Twig\TwigFunction;

class GroupNavigationExtensionTest extends TestCase
{
    public function testTemplateHelpersAreExposed(): void
    {
        $extension = new GroupNavigationExtension(new GroupNavigation(), new LabelFormatter(), true);

        self::assertSame(
            ['lle_config_groups', 'lle_config_active_group', 'lle_config_group_configs', 'lle_config_using_tenant'],
            array_map(static fn (TwigFunction $function): string => $function->getName(), $extension->getFunctions()),
        );
        self::assertSame(
            ['lle_config_humanize'],
            array_map(static fn (TwigFilter $filter): string => $filter->getName(), $extension->getFilters()),
        );
    }

    public function testTenantModeFollowsTheBundleConfiguration(): void
    {
        $withTenant = new GroupNavigationExtension(new GroupNavigation(), new LabelFormatter(), true);
        $withoutTenant = new GroupNavigationExtension(new GroupNavigation(), new LabelFormatter(), false);

        self::assertTrue($withTenant->isUsingTenant());
        self::assertFalse($withoutTenant->isUsingTenant());
    }
}
