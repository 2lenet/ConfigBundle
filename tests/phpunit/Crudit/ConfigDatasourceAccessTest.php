<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Tests\Crudit;

use Lle\ConfigBundle\Contracts\ConfigInterface;
use Lle\ConfigBundle\Crudit\Datasource\ConfigDatasource;
use Lle\ConfigBundle\Security\ConfigAccessChecker;
use Lle\ConfigBundle\Tests\Fixtures\Config;
use Lle\ConfigBundle\Tests\Support\DoctrineTestCase;
use Lle\CruditBundle\Filter\FilterState;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Server side of the group roles: what the datasource lists, loads and saves.
 */
class ConfigDatasourceAccessTest extends DoctrineTestCase
{
    private const GROUP_PAYMENT = 'Saferpay';
    private const GROUP_MAIL = 'Mail';
    private const LABEL_LOGIN = 'login';
    private const LABEL_PASSWORD = 'password';
    private const LABEL_SENDER = 'sender';

    private const ORIGINAL_VALUE = 'original';
    private const NEW_VALUE = 'changed';

    private const ROLE_SHOW_PAYMENT = 'ROLE_CONFIG_ACTION_SHOW_GROUP_SAFERPAY';
    private const ROLE_EDIT_PAYMENT = 'ROLE_CONFIG_ACTION_EDIT_GROUP_SAFERPAY';
    private const ROLE_SECRET_PAYMENT = 'ROLE_CONFIG_ACTION_SECRET_GROUP_SAFERPAY';
    private const ROLE_EDIT_MAIL = 'ROLE_CONFIG_ACTION_EDIT_GROUP_MAIL';

    private Config $login;
    private Config $password;
    private Config $sender;

    protected function setUp(): void
    {
        parent::setUp();

        $this->login = $this->saveConfig(Config::create(self::GROUP_PAYMENT, self::LABEL_LOGIN)->setValueString(self::ORIGINAL_VALUE));
        $this->password = $this->saveConfig(
            Config::create(self::GROUP_PAYMENT, self::LABEL_PASSWORD)
                ->setValueType(ConfigInterface::PASSWORD)
                ->setValueString(self::ORIGINAL_VALUE),
        );
        $this->sender = $this->saveConfig(Config::create(self::GROUP_MAIL, self::LABEL_SENDER)->setValueString(self::ORIGINAL_VALUE));
    }

    public function testListContainsEveryGroupWithoutAccessChecker(): void
    {
        $datasource = $this->createDatasource(null);

        self::assertSame([self::LABEL_LOGIN, self::LABEL_PASSWORD, self::LABEL_SENDER], $this->listLabels($datasource));
    }

    public function testListContainsEveryGroupWhenGroupRolesAreDisabled(): void
    {
        $datasource = $this->createDatasource($this->createAccessChecker(false, []));

        self::assertSame([self::LABEL_LOGIN, self::LABEL_PASSWORD, self::LABEL_SENDER], $this->listLabels($datasource));
    }

    public function testListOnlyContainsVisibleGroups(): void
    {
        $datasource = $this->createDatasource($this->createAccessChecker(true, [self::ROLE_SHOW_PAYMENT]));

        self::assertSame([self::LABEL_LOGIN, self::LABEL_PASSWORD], $this->listLabels($datasource));
    }

    public function testAHiddenGroupCannotBeLoadedById(): void
    {
        $datasource = $this->createDatasource($this->createAccessChecker(true, [self::ROLE_SHOW_PAYMENT]));

        self::assertSame($this->login, $datasource->get((int) $this->login->getId()));
        self::assertNull($datasource->get((int) $this->sender->getId()));
    }

    public function testValueIsSavedWithEditRole(): void
    {
        $datasource = $this->createDatasource($this->createAccessChecker(true, [self::ROLE_EDIT_PAYMENT]));

        self::assertTrue($datasource->save($this->login->setValueString(self::NEW_VALUE)));

        self::assertSame(self::NEW_VALUE, $this->findStoredValue(self::GROUP_PAYMENT, self::LABEL_LOGIN));
    }

    public function testSaveIsRefusedWithoutEditRole(): void
    {
        $datasource = $this->createDatasource($this->createAccessChecker(true, [self::ROLE_SHOW_PAYMENT]));
        $login = $this->login->setValueString(self::NEW_VALUE);

        $caught = $this->saveAndCatch($datasource, $login);

        self::assertInstanceOf(AccessDeniedException::class, $caught);
        $this->assertNothingWritten($this->login, self::GROUP_PAYMENT, self::LABEL_LOGIN);
    }

    public function testSecretIsNotSavedWithoutSecretRole(): void
    {
        $datasource = $this->createDatasource($this->createAccessChecker(true, [self::ROLE_EDIT_PAYMENT]));
        $password = $this->password->setValueString(self::NEW_VALUE);

        $caught = $this->saveAndCatch($datasource, $password);

        self::assertInstanceOf(AccessDeniedException::class, $caught);
        $this->assertNothingWritten($this->password, self::GROUP_PAYMENT, self::LABEL_PASSWORD);
    }

    public function testSecretIsSavedWithEditAndSecretRoles(): void
    {
        $datasource = $this->createDatasource($this->createAccessChecker(true, [self::ROLE_EDIT_PAYMENT, self::ROLE_SECRET_PAYMENT]));

        self::assertTrue($datasource->save($this->password->setValueString(self::NEW_VALUE)));

        self::assertSame(self::NEW_VALUE, $this->findStoredValue(self::GROUP_PAYMENT, self::LABEL_PASSWORD));
    }

    public function testASecretCannotBeTurnedIntoAPlainValue(): void
    {
        $datasource = $this->createDatasource($this->createAccessChecker(true, [self::ROLE_EDIT_PAYMENT]));
        $password = $this->password->setValueType(ConfigInterface::STRING)->setValueString(self::NEW_VALUE);

        $caught = $this->saveAndCatch($datasource, $password);

        self::assertInstanceOf(AccessDeniedException::class, $caught);
        self::assertSame(ConfigInterface::PASSWORD, $this->password->getValueType());
        $this->assertNothingWritten($this->password, self::GROUP_PAYMENT, self::LABEL_PASSWORD);
    }

    public function testAConfigCannotBeMovedToAnotherGroup(): void
    {
        $datasource = $this->createDatasource($this->createAccessChecker(true, [self::ROLE_EDIT_PAYMENT, self::ROLE_EDIT_MAIL]));
        $sender = $this->sender->setGroup(self::GROUP_PAYMENT);

        $caught = $this->saveAndCatch($datasource, $sender);

        self::assertInstanceOf(AccessDeniedException::class, $caught);
        self::assertSame(self::GROUP_MAIL, $this->sender->getGroup());
    }

    private function saveAndCatch(ConfigDatasource $datasource, Config $config): ?AccessDeniedException
    {
        $caught = null;
        try {
            $datasource->save($config);
        } catch (AccessDeniedException $exception) {
            $caught = $exception;
        }

        return $caught;
    }

    /**
     * The entity is reloaded (a later flush() of the request must not write the refused change) and the database is unchanged.
     */
    private function assertNothingWritten(Config $config, string $group, string $label): void
    {
        self::assertSame(self::ORIGINAL_VALUE, $config->getValueString());
        $this->entityManager->flush();

        self::assertSame(self::ORIGINAL_VALUE, $this->findStoredValue($group, $label));
    }

    /**
     * @param string[] $grantedRoles
     */
    private function createAccessChecker(bool $groupRoles, array $grantedRoles): ConfigAccessChecker
    {
        $authorizationChecker = $this->createStub(AuthorizationCheckerInterface::class);
        $authorizationChecker
            ->method('isGranted')
            ->willReturnCallback(static fn (mixed $attribute): bool => in_array($attribute, $grantedRoles, true));

        return new ConfigAccessChecker($authorizationChecker, $groupRoles);
    }

    private function createDatasource(?ConfigAccessChecker $accessChecker): ConfigDatasource
    {
        return new ConfigDatasource(
            $this->entityManager,
            $this->createStub(FilterState::class),
            $this->cacheManager,
            new ParameterBag(['lle_config.using_tenant' => false]),
            null,
            $accessChecker,
        );
    }

    /**
     * @return string[]
     */
    private function listLabels(ConfigDatasource $datasource): array
    {
        $labels = [];
        foreach ($datasource->list(null) as $config) {
            $labels[] = (string) $config->getLabel();
        }
        sort($labels);

        return $labels;
    }

    private function findStoredValue(string $group, string $label): mixed
    {
        return $this->entityManager->getConnection()->fetchOne(
            'SELECT value_string FROM ' . self::CONFIG_TABLE . ' WHERE "group" = ? AND label = ?',
            [$group, $label],
        );
    }
}
