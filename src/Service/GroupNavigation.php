<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Service;

use Lle\ConfigBundle\Contracts\ConfigInterface;
use Lle\ConfigBundle\Dto\ConfigGroup;

/**
 * Builds the group sidebar of the configuration list: one group is displayed at a time.
 */
class GroupNavigation
{
    /**
     * @param iterable<ConfigInterface> $configs
     *
     * @return ConfigGroup[]
     */
    public function getGroups(iterable $configs, ?string $requestedGroup): array
    {
        $counts = $this->countByGroup($configs);
        $activeGroup = $this->resolveActiveGroup($counts, $requestedGroup);

        $groups = [];
        foreach ($counts as $name => $count) {
            $groups[] = new ConfigGroup((string) $name, $count, $name === $activeGroup);
        }

        return $groups;
    }

    /**
     * @param iterable<ConfigInterface> $configs
     */
    public function getActiveGroup(iterable $configs, ?string $requestedGroup): ?string
    {
        return $this->resolveActiveGroup($this->countByGroup($configs), $requestedGroup);
    }

    /**
     * @template T of ConfigInterface
     *
     * @param iterable<T> $configs
     *
     * @return T[]
     */
    public function filterByGroup(iterable $configs, ?string $group): array
    {
        $filtered = [];
        foreach ($configs as $config) {
            if ($config->getGroup() === $group) {
                $filtered[] = $config;
            }
        }

        return $filtered;
    }

    /**
     * @param iterable<ConfigInterface> $configs
     *
     * @return array<string, int> number of configurations per group, sorted by group name
     */
    protected function countByGroup(iterable $configs): array
    {
        $counts = [];
        foreach ($configs as $config) {
            $group = (string) $config->getGroup();
            $counts[$group] = ($counts[$group] ?? 0) + 1;
        }

        uksort($counts, static fn (int|string $left, int|string $right): int => strnatcasecmp((string) $left, (string) $right));

        return $counts;
    }

    /**
     * @param array<string, int> $counts
     */
    protected function resolveActiveGroup(array $counts, ?string $requestedGroup): ?string
    {
        if ($requestedGroup !== null && array_key_exists($requestedGroup, $counts)) {
            return $requestedGroup;
        }

        $firstGroup = array_key_first($counts);

        return $firstGroup === null ? null : (string) $firstGroup;
    }
}
