<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Tests\Security;

use Lle\ConfigBundle\Contracts\ConfigInterface;
use Lle\ConfigBundle\Security\ConfigAccessChecker;
use Lle\ConfigBundle\Security\ConfigCredentialWarmup;
use Lle\ConfigBundle\Tests\Fixtures\Config;
use Lle\ConfigBundle\Tests\Support\DoctrineTestCase;
use Lle\CredentialBundle\Entity\Credential;
use Lle\CredentialBundle\Factory\CredentialFactory;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ConfigCredentialWarmupTest extends DoctrineTestCase
{
    private const CREDENTIAL_ENTITY_DIRECTORY = __DIR__ . '/../../../vendor/2lenet/credential-bundle/src/Entity';

    private const GROUP_PAYMENT = 'Payment Service';
    private const GROUP_MAIL = 'Mail';
    private const TENANT_ID = 7;

    public function testNoRoleIsCreatedWhenGroupRolesAreDisabled(): void
    {
        $this->saveConfig($this->createConfig(self::GROUP_PAYMENT, 'login', null));

        $this->createWarmup(false)->warmUp();

        self::assertSame([], $this->findRoles());
    }

    public function testRolesAreCreatedForEachGroupAndForAllGroups(): void
    {
        $this->saveConfig($this->createConfig(self::GROUP_PAYMENT, 'login', null));
        $this->saveConfig($this->createConfig(self::GROUP_PAYMENT, 'password', null));
        $this->saveConfig($this->createConfig(self::GROUP_PAYMENT, 'password', self::TENANT_ID));
        $this->saveConfig($this->createConfig(self::GROUP_MAIL, 'sender', null));

        $this->createWarmup(true)->warmUp();

        $expectedRoles = [
            'ROLE_CONFIG_ACTION_EDIT_ALL',
            'ROLE_CONFIG_ACTION_EDIT_GROUP_MAIL',
            'ROLE_CONFIG_ACTION_EDIT_GROUP_PAYMENT_SERVICE',
            'ROLE_CONFIG_ACTION_SECRET_ALL',
            'ROLE_CONFIG_ACTION_SECRET_GROUP_MAIL',
            'ROLE_CONFIG_ACTION_SECRET_GROUP_PAYMENT_SERVICE',
            'ROLE_CONFIG_ACTION_SHOW_ALL',
            'ROLE_CONFIG_ACTION_SHOW_GROUP_MAIL',
            'ROLE_CONFIG_ACTION_SHOW_GROUP_PAYMENT_SERVICE',
        ];
        self::assertSame($expectedRoles, $this->findRoles());
    }

    public function testCredentialsAreFiledInTheConfigSectionWithAReadableLabel(): void
    {
        $this->saveConfig($this->createConfig(self::GROUP_PAYMENT, 'login', null));

        $this->createWarmup(true)->warmUp();

        $groupCredential = $this->findCredential('ROLE_CONFIG_ACTION_EDIT_GROUP_PAYMENT_SERVICE');
        self::assertSame(ConfigCredentialWarmup::SECTION, $groupCredential->getSection());
        self::assertSame('Payment Service — EDIT', $groupCredential->getLabel());
        self::assertSame(ConfigCredentialWarmup::CREDENTIAL_TYPE, $groupCredential->getType());

        $globalCredential = $this->findCredential('ROLE_CONFIG_ACTION_SHOW_ALL');
        self::assertSame(ConfigCredentialWarmup::LABEL_ALL_GROUPS . ' — SHOW', $globalCredential->getLabel());
    }

    public function testRunningTheWarmupTwiceDoesNotDuplicateRoles(): void
    {
        $this->saveConfig($this->createConfig(self::GROUP_MAIL, 'sender', null));
        $warmup = $this->createWarmup(true);

        $warmup->warmUp();
        $warmup->warmUp();

        self::assertCount(6, $this->findRoles());
    }

    protected function getEntityDirectories(): array
    {
        return [...parent::getEntityDirectories(), self::CREDENTIAL_ENTITY_DIRECTORY];
    }

    private function createWarmup(bool $groupRoles): ConfigCredentialWarmup
    {
        $accessChecker = new ConfigAccessChecker($this->createStub(AuthorizationCheckerInterface::class), $groupRoles);

        return new ConfigCredentialWarmup($this->entityManager, new CredentialFactory($this->entityManager), $accessChecker);
    }

    private function createConfig(string $group, string $label, ?int $tenantId): Config
    {
        $config = new Config();
        $config
            ->setGroup($group)
            ->setLabel($label)
            ->setValueType(ConfigInterface::STRING)
            ->setValueString('')
            ->setTenantId($tenantId);

        return $config;
    }

    /**
     * @return string[]
     */
    private function findRoles(): array
    {
        $roles = array_map(
            static fn (Credential $credential): string => (string) $credential->getRole(),
            $this->entityManager->getRepository(Credential::class)->findAll(),
        );
        sort($roles);

        return $roles;
    }

    private function findCredential(string $role): Credential
    {
        $credential = $this->entityManager->getRepository(Credential::class)->findOneBy(['role' => $role]);
        self::assertInstanceOf(Credential::class, $credential);

        return $credential;
    }
}
