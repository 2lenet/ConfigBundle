<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Tests\Twig;

use Lle\ConfigBundle\Contracts\ConfigInterface;
use Lle\ConfigBundle\Security\ConfigAccessChecker;
use Lle\ConfigBundle\Tests\Fixtures\Config;
use Lle\ConfigBundle\Twig\ConfigAccessExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

/**
 * Rendering of a value (list and Crudit show page) according to the group roles.
 */
class ValueTemplateTest extends TestCase
{
    private const VIEWS_DIRECTORY = __DIR__ . '/../../../src/Resources/views';
    private const TEMPLATE = '@LleConfig/_value.html.twig';

    private const GROUP = 'Saferpay';
    private const VALUE = 'stored-value-42';
    private const MASK = '••••••••';

    private const EIP_MARKUP = 'crudit-eip';
    private const FORM_MARKUP = '<form';

    private const ROLE_SHOW = 'ROLE_CONFIG_ACTION_SHOW_GROUP_SAFERPAY';
    private const ROLE_EDIT = 'ROLE_CONFIG_ACTION_EDIT_GROUP_SAFERPAY';
    private const ROLE_SECRET = 'ROLE_CONFIG_ACTION_SECRET_GROUP_SAFERPAY';

    public function testValueIsEditableWhenGroupRolesAreDisabled(): void
    {
        $html = $this->render($this->createString(), false, []);

        self::assertStringContainsString(self::EIP_MARKUP, $html);
        self::assertStringContainsString(self::FORM_MARKUP, $html);
        self::assertStringContainsString(self::VALUE, $html);
    }

    public function testPasswordIsEditableWhenGroupRolesAreDisabled(): void
    {
        $html = $this->render($this->createPassword(self::VALUE), false, []);

        self::assertStringContainsString(self::EIP_MARKUP, $html);
        self::assertStringContainsString(self::MASK, $html);
    }

    public function testValueIsReadOnlyWithoutEditRole(): void
    {
        $html = $this->render($this->createString(), true, [self::ROLE_SHOW]);

        self::assertStringContainsString(self::VALUE, $html);
        self::assertStringNotContainsString(self::EIP_MARKUP, $html);
        self::assertStringNotContainsString(self::FORM_MARKUP, $html);
    }

    public function testValueIsEditableWithEditRole(): void
    {
        $html = $this->render($this->createString(), true, [self::ROLE_EDIT]);

        self::assertStringContainsString(self::EIP_MARKUP, $html);
        self::assertStringContainsString('data-field="valueString"', $html);
        self::assertStringContainsString('/config/12/editdata', $html);
    }

    public function testBooleanIsADisabledSwitchWithoutEditRole(): void
    {
        $config = $this->createConfig(ConfigInterface::BOOL)->setValueBool(true);

        $html = $this->render($config, true, [self::ROLE_SHOW]);

        self::assertStringNotContainsString(self::EIP_MARKUP, $html);
        self::assertStringContainsString('disabled', $html);
        self::assertStringContainsString('checked', $html);
    }

    public function testTextIsDisplayedWithoutTextareaWhenReadOnly(): void
    {
        $config = $this->createConfig(ConfigInterface::TEXT)->setValueText(self::VALUE);

        $html = $this->render($config, true, [self::ROLE_SHOW]);

        self::assertStringContainsString(self::VALUE, $html);
        self::assertStringNotContainsString('<textarea', $html);
    }

    public function testSecretIsNeverInTheHtmlWithoutSecretRole(): void
    {
        $html = $this->render($this->createPassword(self::VALUE), true, [self::ROLE_EDIT]);

        self::assertStringNotContainsString(self::VALUE, $html);
        self::assertStringNotContainsString(self::EIP_MARKUP, $html);
        self::assertStringContainsString(self::MASK, $html);
    }

    public function testEmptySecretShowsNoMask(): void
    {
        $html = $this->render($this->createPassword(''), true, [self::ROLE_SHOW]);

        self::assertStringNotContainsString(self::MASK, $html);
    }

    public function testSecretCanBeRevealedWithSecretRole(): void
    {
        $html = $this->render($this->createPassword(self::VALUE), true, [self::ROLE_SHOW, self::ROLE_SECRET]);

        self::assertStringContainsString(self::VALUE, $html);
        self::assertStringContainsString(self::MASK, $html);
        self::assertStringNotContainsString(self::EIP_MARKUP, $html);
    }

    public function testSecretIsEditableWithEditAndSecretRoles(): void
    {
        $html = $this->render($this->createPassword(self::VALUE), true, [self::ROLE_EDIT, self::ROLE_SECRET]);

        self::assertStringContainsString(self::EIP_MARKUP, $html);
        self::assertStringContainsString('type="password"', $html);
        self::assertStringContainsString(self::VALUE, $html);
    }

    /**
     * @param string[] $grantedRoles
     */
    private function render(Config $config, bool $groupRoles, array $grantedRoles): string
    {
        $authorizationChecker = $this->createStub(AuthorizationCheckerInterface::class);
        $authorizationChecker
            ->method('isGranted')
            ->willReturnCallback(static fn (mixed $attribute): bool => in_array($attribute, $grantedRoles, true));

        $loader = new FilesystemLoader();
        $loader->addPath(self::VIEWS_DIRECTORY, 'LleConfig');
        $twig = new Environment($loader, ['strict_variables' => true]);
        $twig->addExtension(new ConfigAccessExtension(new ConfigAccessChecker($authorizationChecker, $groupRoles)));
        $twig->addFunction(new TwigFunction(
            'path',
            static fn (string $route, array $parameters): string => sprintf('/config/%s/editdata', $parameters['id']),
        ));

        return $twig->render(self::TEMPLATE, ['view' => ['resource' => $config]]);
    }

    private function createConfig(string $valueType): Config
    {
        $config = Config::create(self::GROUP, 'login');
        $config->setId(12);
        $config->setValueType($valueType);

        return $config;
    }

    private function createString(): Config
    {
        return $this->createConfig(ConfigInterface::STRING)->setValueString(self::VALUE);
    }

    private function createPassword(string $value): Config
    {
        return $this->createConfig(ConfigInterface::PASSWORD)->setValueString($value);
    }
}
