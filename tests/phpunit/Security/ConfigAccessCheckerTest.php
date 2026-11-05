<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Tests\Security;

use Lle\ConfigBundle\Contracts\ConfigInterface;
use Lle\ConfigBundle\Security\ConfigAccessChecker;
use Lle\ConfigBundle\Tests\Fixtures\Config;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class ConfigAccessCheckerTest extends TestCase
{
    private const GROUP_PAYMENT = 'Payment Service';
    private const GROUP_MAIL = 'Mail';

    private const ROLE_SHOW_PAYMENT = 'ROLE_CONFIG_ACTION_SHOW_GROUP_PAYMENT_SERVICE';
    private const ROLE_EDIT_PAYMENT = 'ROLE_CONFIG_ACTION_EDIT_GROUP_PAYMENT_SERVICE';
    private const ROLE_SECRET_PAYMENT = 'ROLE_CONFIG_ACTION_SECRET_GROUP_PAYMENT_SERVICE';
    private const ROLE_SHOW_ALL = 'ROLE_CONFIG_ACTION_SHOW_ALL';
    private const ROLE_EDIT_ALL = 'ROLE_CONFIG_ACTION_EDIT_ALL';
    private const ROLE_SECRET_ALL = 'ROLE_CONFIG_ACTION_SECRET_ALL';
    private const ROLE_EMPTY_SLUG_SHOW = 'ROLE_CONFIG_ACTION_SHOW_GROUP_';

    private const PARAMETER_GROUP_ROLES = 'lle_config.group_roles';

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function groupSlugProvider(): iterable
    {
        yield 'space' => ['Payment Service', 'PAYMENT_SERVICE'];
        yield 'dash' => ['Wordline-cloud-terminal', 'WORDLINE_CLOUD_TERMINAL'];
        yield 'underscore' => ['opening_hours', 'OPENING_HOURS'];
        yield 'accent' => ['Généralités', 'GENERALITES'];
        yield 'repeated separators' => ['  Mail // SMTP  ', 'MAIL_SMTP'];
        yield 'digits' => ['Netaxept2', 'NETAXEPT2'];
    }

    #[DataProvider('groupSlugProvider')]
    public function testGroupSlug(string $group, string $expectedSlug): void
    {
        $checker = $this->createChecker(true, []);

        self::assertSame($expectedSlug, $checker->getGroupSlug($group));
    }

    public function testRolesForAGroup(): void
    {
        $checker = $this->createChecker(true, []);

        self::assertSame(
            [
                ConfigAccessChecker::ACTION_SHOW => self::ROLE_SHOW_PAYMENT,
                ConfigAccessChecker::ACTION_EDIT => self::ROLE_EDIT_PAYMENT,
                ConfigAccessChecker::ACTION_SECRET => self::ROLE_SECRET_PAYMENT,
            ],
            $checker->getRolesFor(self::GROUP_PAYMENT),
        );
    }

    public function testGlobalRoles(): void
    {
        $checker = $this->createChecker(true, []);

        self::assertSame(
            [
                ConfigAccessChecker::ACTION_SHOW => self::ROLE_SHOW_ALL,
                ConfigAccessChecker::ACTION_EDIT => self::ROLE_EDIT_ALL,
                ConfigAccessChecker::ACTION_SECRET => self::ROLE_SECRET_ALL,
            ],
            $checker->getGlobalRoles(),
        );
    }

    public function testEverythingIsAllowedWhenGroupRolesAreDisabled(): void
    {
        $checker = $this->createChecker(false, []);

        self::assertFalse($checker->isEnabled());
        self::assertTrue($checker->canView(self::GROUP_PAYMENT));
        self::assertTrue($checker->canEdit(self::GROUP_PAYMENT));
        self::assertTrue($checker->canSeeSecret(self::GROUP_PAYMENT));
    }

    public function testNothingIsAllowedWithoutRole(): void
    {
        $checker = $this->createChecker(true, []);

        self::assertTrue($checker->isEnabled());
        self::assertFalse($checker->canView(self::GROUP_PAYMENT));
        self::assertFalse($checker->canEdit(self::GROUP_PAYMENT));
        self::assertFalse($checker->canSeeSecret(self::GROUP_PAYMENT));
    }

    public function testShowRoleOfAGroupOnlyShowsThatGroup(): void
    {
        $checker = $this->createChecker(true, [self::ROLE_SHOW_PAYMENT]);

        self::assertTrue($checker->canView(self::GROUP_PAYMENT));
        self::assertFalse($checker->canEdit(self::GROUP_PAYMENT));
        self::assertFalse($checker->canSeeSecret(self::GROUP_PAYMENT));
        self::assertFalse($checker->canView(self::GROUP_MAIL));
    }

    public function testEditRoleAlsoShowsTheGroup(): void
    {
        $checker = $this->createChecker(true, [self::ROLE_EDIT_PAYMENT]);

        self::assertTrue($checker->canView(self::GROUP_PAYMENT));
        self::assertTrue($checker->canEdit(self::GROUP_PAYMENT));
        self::assertFalse($checker->canSeeSecret(self::GROUP_PAYMENT));
    }

    public function testSecretRoleDoesNotShowTheGroup(): void
    {
        $checker = $this->createChecker(true, [self::ROLE_SECRET_PAYMENT]);

        self::assertFalse($checker->canView(self::GROUP_PAYMENT));
        self::assertTrue($checker->canSeeSecret(self::GROUP_PAYMENT));
    }

    public function testGlobalRolesApplyToEveryGroup(): void
    {
        $checker = $this->createChecker(true, [self::ROLE_SHOW_ALL, self::ROLE_EDIT_ALL, self::ROLE_SECRET_ALL]);

        foreach ([self::GROUP_PAYMENT, self::GROUP_MAIL] as $group) {
            self::assertTrue($checker->canView($group));
            self::assertTrue($checker->canEdit($group));
            self::assertTrue($checker->canSeeSecret($group));
        }
    }

    public function testSecretValueOfAConfig(): void
    {
        $password = $this->createConfig(ConfigInterface::PASSWORD);
        $string = $this->createConfig(ConfigInterface::STRING);

        $showOnly = $this->createChecker(true, [self::ROLE_SHOW_PAYMENT]);
        self::assertTrue($showOnly->canSeeValue($string));
        self::assertFalse($showOnly->canSeeValue($password));

        $withSecret = $this->createChecker(true, [self::ROLE_SHOW_PAYMENT, self::ROLE_SECRET_PAYMENT]);
        self::assertTrue($withSecret->canSeeValue($password));
    }

    public function testEditValueOfAConfig(): void
    {
        $password = $this->createConfig(ConfigInterface::PASSWORD);
        $string = $this->createConfig(ConfigInterface::STRING);

        $editOnly = $this->createChecker(true, [self::ROLE_EDIT_PAYMENT]);
        self::assertTrue($editOnly->canEditValue($string));
        self::assertFalse($editOnly->canEditValue($password));

        $secretOnly = $this->createChecker(true, [self::ROLE_SHOW_PAYMENT, self::ROLE_SECRET_PAYMENT]);
        self::assertFalse($secretOnly->canEditValue($password));

        $editAndSecret = $this->createChecker(true, [self::ROLE_EDIT_PAYMENT, self::ROLE_SECRET_PAYMENT]);
        self::assertTrue($editAndSecret->canEditValue($password));
    }

    public function testConfigValuesAreAllowedWhenGroupRolesAreDisabled(): void
    {
        $password = $this->createConfig(ConfigInterface::PASSWORD);
        $checker = $this->createChecker(false, []);

        self::assertTrue($checker->canSeeValue($password));
        self::assertTrue($checker->canEditValue($password));
    }

    public function testEverythingIsAllowedWithoutSecurityWhenGroupRolesAreDisabled(): void
    {
        $checker = new ConfigAccessChecker(null, false);

        self::assertTrue($checker->canView(self::GROUP_PAYMENT));
        self::assertTrue($checker->canEditValue($this->createConfig(ConfigInterface::PASSWORD)));
    }

    /**
     * Group roles enabled in an application without the SecurityBundle: fail closed.
     */
    public function testNothingIsAllowedWithoutSecurityWhenGroupRolesAreEnabled(): void
    {
        $checker = new ConfigAccessChecker(null, true);

        self::assertFalse($checker->canView(self::GROUP_PAYMENT));
        self::assertFalse($checker->canEdit(self::GROUP_PAYMENT));
        self::assertFalse($checker->canSeeSecret(self::GROUP_PAYMENT));
    }

    /**
     * The checker is always registered by services.yaml: it must compile without security.authorization_checker.
     */
    public function testCheckerIsAutowiredWithoutTheSecurityBundle(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter(self::PARAMETER_GROUP_ROLES, true);
        $container->register(ConfigAccessChecker::class)->setAutowired(true)->setPublic(true);
        $container->compile();

        $checker = $container->get(ConfigAccessChecker::class);

        self::assertInstanceOf(ConfigAccessChecker::class, $checker);
        self::assertFalse($checker->canView(self::GROUP_PAYMENT));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function emptySlugGroupProvider(): iterable
    {
        yield 'symbols' => ['***'];
        yield 'spaces' => ['   '];
        yield 'empty' => [''];
    }

    #[DataProvider('emptySlugGroupProvider')]
    public function testAGroupWithAnEmptySlugHasNoGroupRole(string $group): void
    {
        $checker = $this->createChecker(true, [self::ROLE_EMPTY_SLUG_SHOW]);

        self::assertSame([], $checker->getRolesFor($group));
        self::assertFalse($checker->canView($group));
    }

    #[DataProvider('emptySlugGroupProvider')]
    public function testAGroupWithAnEmptySlugIsReachedByTheGlobalRoles(string $group): void
    {
        $checker = $this->createChecker(true, [self::ROLE_SHOW_ALL]);

        self::assertTrue($checker->canView($group));
    }

    /**
     * @param string[] $grantedRoles
     */
    private function createChecker(bool $groupRoles, array $grantedRoles): ConfigAccessChecker
    {
        $authorizationChecker = $this->createStub(AuthorizationCheckerInterface::class);
        $authorizationChecker
            ->method('isGranted')
            ->willReturnCallback(static fn (mixed $attribute): bool => in_array($attribute, $grantedRoles, true));

        return new ConfigAccessChecker($authorizationChecker, $groupRoles);
    }

    private function createConfig(string $valueType): Config
    {
        $config = new Config();
        $config
            ->setGroup(self::GROUP_PAYMENT)
            ->setLabel('code')
            ->setValueType($valueType);

        return $config;
    }
}
