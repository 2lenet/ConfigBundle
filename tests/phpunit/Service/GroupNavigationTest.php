<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Tests\Service;

use Lle\ConfigBundle\Dto\ConfigGroup;
use Lle\ConfigBundle\Service\GroupNavigation;
use Lle\ConfigBundle\Tests\Fixtures\Config;
use PHPUnit\Framework\TestCase;

class GroupNavigationTest extends TestCase
{
    private const GROUP_MAIL = 'Mail';
    private const GROUP_OPENING_HOURS = 'opening_hours';
    private const GROUP_SAFERPAY = 'Saferpay';

    public function testGroupsAreSortedAlphabeticallyWithTheirParameterCount(): void
    {
        $navigation = new GroupNavigation();

        $groups = $navigation->getGroups($this->createConfigs(), null);

        self::assertSame(
            [self::GROUP_MAIL, self::GROUP_OPENING_HOURS, self::GROUP_SAFERPAY],
            array_map(static fn (ConfigGroup $group): string => $group->getName(), $groups),
        );
        self::assertSame(
            [2, 1, 3],
            array_map(static fn (ConfigGroup $group): int => $group->getCount(), $groups),
        );
    }

    public function testRequestedGroupIsActive(): void
    {
        $navigation = new GroupNavigation();

        $activeGroup = $navigation->getActiveGroup($this->createConfigs(), self::GROUP_SAFERPAY);
        $groups = $navigation->getGroups($this->createConfigs(), self::GROUP_SAFERPAY);

        self::assertSame(self::GROUP_SAFERPAY, $activeGroup);
        self::assertSame(
            [false, false, true],
            array_map(static fn (ConfigGroup $group): bool => $group->isActive(), $groups),
        );
    }

    public function testFirstGroupIsActiveWhenNoneIsRequested(): void
    {
        $navigation = new GroupNavigation();

        self::assertSame(self::GROUP_MAIL, $navigation->getActiveGroup($this->createConfigs(), null));
        self::assertSame(self::GROUP_MAIL, $navigation->getActiveGroup($this->createConfigs(), ''));
    }

    public function testFirstGroupIsActiveWhenTheRequestedGroupDoesNotExist(): void
    {
        $navigation = new GroupNavigation();

        self::assertSame(self::GROUP_MAIL, $navigation->getActiveGroup($this->createConfigs(), 'Unknown'));
    }

    public function testGroupNameIsMatchedExactly(): void
    {
        $navigation = new GroupNavigation();

        self::assertSame(self::GROUP_MAIL, $navigation->getActiveGroup($this->createConfigs(), 'saferpay'));
    }

    public function testNoActiveGroupWithoutConfiguration(): void
    {
        $navigation = new GroupNavigation();

        self::assertNull($navigation->getActiveGroup([], self::GROUP_SAFERPAY));
        self::assertSame([], $navigation->getGroups([], self::GROUP_SAFERPAY));
    }

    public function testOnlyConfigurationsOfTheActiveGroupAreKept(): void
    {
        $navigation = new GroupNavigation();

        $configs = $navigation->filterByGroup($this->createConfigs(), self::GROUP_MAIL);

        self::assertSame(
            ['host', 'port'],
            array_map(static fn (Config $config): ?string => $config->getLabel(), $configs),
        );
    }

    /**
     * @return Config[]
     */
    private function createConfigs(): array
    {
        return [
            Config::create(self::GROUP_SAFERPAY, 'sandbox'),
            Config::create(self::GROUP_MAIL, 'host'),
            Config::create(self::GROUP_SAFERPAY, 'username', 4),
            Config::create(self::GROUP_OPENING_HOURS, 'from (0 - 23)'),
            Config::create(self::GROUP_MAIL, 'port'),
            Config::create(self::GROUP_SAFERPAY, 'password'),
        ];
    }
}
