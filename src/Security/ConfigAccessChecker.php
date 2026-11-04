<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Security;

use Lle\ConfigBundle\Contracts\ConfigInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

/**
 * Single decision point for the visibility of the configuration, group by group.
 *
 * With the "group_roles" option disabled (default), everything is allowed: access stays
 * controlled by the Crudit roles only (ROLE_CONFIG_INDEX, ROLE_CONFIG_EDIT…).
 *
 * Role format: ROLE_CONFIG_ACTION_<ACTION>_GROUP_<GROUP SLUG>, or ROLE_CONFIG_ACTION_<ACTION>_ALL.
 * EDIT also grants SHOW; a secret (Password) needs SECRET to be read, and EDIT + SECRET to be changed.
 */
class ConfigAccessChecker
{
    public const ACTION_SHOW = 'SHOW';
    public const ACTION_EDIT = 'EDIT';
    public const ACTION_SECRET = 'SECRET';
    public const ACTIONS = [self::ACTION_SHOW, self::ACTION_EDIT, self::ACTION_SECRET];

    public const ROLE_PREFIX = 'ROLE_CONFIG_ACTION_';
    public const SCOPE_GROUP = 'GROUP';
    public const SCOPE_ALL = 'ALL';
    public const SEPARATOR = '_';

    /**
     * @param AuthorizationCheckerInterface|null $authorizationChecker null without the SecurityBundle:
     *                                                               everything is then refused while the option is enabled
     */
    public function __construct(
        protected ?AuthorizationCheckerInterface $authorizationChecker,
        #[Autowire(param: 'lle_config.group_roles')]
        protected bool $groupRoles,
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->groupRoles;
    }

    public function canView(?string $group): bool
    {
        return $this->isAllowed(self::ACTION_SHOW, $group) || $this->canEdit($group);
    }

    public function canEdit(?string $group): bool
    {
        return $this->isAllowed(self::ACTION_EDIT, $group);
    }

    public function canSeeSecret(?string $group): bool
    {
        return $this->isAllowed(self::ACTION_SECRET, $group);
    }

    public function canSeeValue(ConfigInterface $config): bool
    {
        if (!$this->canView($config->getGroup())) {
            return false;
        }

        return !$this->isSecret($config) || $this->canSeeSecret($config->getGroup());
    }

    public function canEditValue(ConfigInterface $config): bool
    {
        if (!$this->canEdit($config->getGroup())) {
            return false;
        }

        return !$this->isSecret($config) || $this->canSeeSecret($config->getGroup());
    }

    public function isSecret(ConfigInterface $config): bool
    {
        return $config->getValueType() === ConfigInterface::PASSWORD;
    }

    /**
     * @return array<string, string> role per action, for one group; none for a group whose slug is empty
     *                               (only symbols): it is only reached by the *_ALL roles
     */
    public function getRolesFor(string $group): array
    {
        $roles = [];
        foreach (self::ACTIONS as $action) {
            $role = $this->buildGroupRole($action, $group);
            if ($role !== null) {
                $roles[$action] = $role;
            }
        }

        return $roles;
    }

    /**
     * @return array<string, string> role per action, for every group
     */
    public function getGlobalRoles(): array
    {
        $roles = [];
        foreach (self::ACTIONS as $action) {
            $roles[$action] = $this->buildGlobalRole($action);
        }

        return $roles;
    }

    /**
     * "Payment Service" => "PAYMENT_SERVICE", "Généralités" => "GENERALITES".
     */
    public function getGroupSlug(string $group): string
    {
        return (new AsciiSlugger())->slug($group, self::SEPARATOR)->upper()->toString();
    }

    protected function isAllowed(string $action, ?string $group): bool
    {
        if (!$this->groupRoles) {
            return true;
        }

        if ($this->authorizationChecker === null) {
            return false;
        }

        if ($this->authorizationChecker->isGranted($this->buildGlobalRole($action))) {
            return true;
        }

        $groupRole = $group === null ? null : $this->buildGroupRole($action, $group);

        return $groupRole !== null && $this->authorizationChecker->isGranted($groupRole);
    }

    protected function buildGroupRole(string $action, string $group): ?string
    {
        $slug = $this->getGroupSlug($group);
        if ($slug === '') {
            return null;
        }

        return implode(self::SEPARATOR, [self::ROLE_PREFIX . $action, self::SCOPE_GROUP, $slug]);
    }

    protected function buildGlobalRole(string $action): string
    {
        return implode(self::SEPARATOR, [self::ROLE_PREFIX . $action, self::SCOPE_ALL]);
    }
}
