<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Security;

use Doctrine\ORM\EntityManagerInterface;
use Lle\ConfigBundle\Contracts\ConfigInterface;
use Lle\CredentialBundle\Contracts\CredentialWarmupInterface;
use Lle\CredentialBundle\Factory\CredentialFactory;
use Lle\CredentialBundle\Service\CredentialWarmupTrait;

/**
 * Declares the ROLE_CONFIG_ACTION_* roles in the credential bundle (lle:credential:warmup):
 * one role per action for each group found in the database, and one per action for all groups.
 *
 * Registered only when the credential bundle is installed; does nothing while "group_roles" is disabled.
 */
class ConfigCredentialWarmup implements CredentialWarmupInterface
{
    use CredentialWarmupTrait;

    public const SECTION = 'CONFIG';
    public const CREDENTIAL_TYPE = 'credential.action';
    public const LABEL_ALL_GROUPS = '*';
    public const LABEL_FORMAT = '%s — %s';

    public function __construct(
        protected EntityManagerInterface $entityManager,
        protected CredentialFactory $credentialFactory,
        protected ConfigAccessChecker $accessChecker,
    ) {
    }

    public function warmUp(): void
    {
        if (!$this->accessChecker->isEnabled()) {
            return;
        }

        $this->createRoles($this->accessChecker->getGlobalRoles(), self::LABEL_ALL_GROUPS);

        foreach ($this->findGroups() as $group) {
            $this->createRoles($this->accessChecker->getRolesFor($group), $group);
        }
    }

    /**
     * @param array<string, string> $roles role per action
     */
    protected function createRoles(array $roles, string $groupLabel): void
    {
        foreach ($roles as $action => $role) {
            $this->checkAndCreateCredential(
                $role,
                self::SECTION,
                sprintf(self::LABEL_FORMAT, $groupLabel, $action),
                statusList: [],
                type: self::CREDENTIAL_TYPE,
            );
        }
    }

    /**
     * @return string[]
     */
    protected function findGroups(): array
    {
        /** @var array<array{group: ?string}> $rows */
        $rows = $this->entityManager->createQueryBuilder()
            ->select('DISTINCT config.group AS group')
            ->from(ConfigInterface::class, 'config')
            ->where('config.group IS NOT NULL')
            ->orderBy('config.group')
            ->getQuery()
            ->getArrayResult();

        return array_map(static fn (array $row): string => (string) $row['group'], $rows);
    }
}
