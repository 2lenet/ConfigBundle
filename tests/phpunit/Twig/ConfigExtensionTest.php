<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Tests\Twig;

use Doctrine\ORM\EntityManagerInterface;
use Lle\ConfigBundle\Contracts\ConfigInterface;
use Lle\ConfigBundle\Repository\AbstractConfigRepository;
use Lle\ConfigBundle\Twig\ConfigExtension;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigExtensionTest extends TestCase
{
    private const GROUP = 'Mail';
    private const NAME = 'host';

    /** @var AbstractConfigRepository&MockObject */
    private AbstractConfigRepository $repository;

    private ConfigExtension $extension;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(AbstractConfigRepository::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->with(ConfigInterface::class)->willReturn($this->repository);

        $this->extension = new ConfigExtension($entityManager);
    }

    public function testPasswordIsRead(): void
    {
        $this->repository->expects(self::once())
            ->method('getPassword')
            ->with(self::GROUP, self::NAME, 'fallback')
            ->willReturn('secret');

        self::assertSame('secret', $this->extension->getConfigValue(ConfigInterface::PASSWORD, self::GROUP, self::NAME, 'fallback'));
    }

    /**
     * @return array<string, array{string, string, bool|float|int|string}>
     */
    public static function missingDefaultProvider(): array
    {
        return [
            'bool' => [ConfigInterface::BOOL, 'getBool', false],
            'string' => [ConfigInterface::STRING, 'getString', ''],
            'text' => [ConfigInterface::TEXT, 'getText', ''],
            'int' => [ConfigInterface::INT, 'getInt', 0],
            'float' => [ConfigInterface::FLOAT, 'getFloat', 0.0],
            'password' => [ConfigInterface::PASSWORD, 'getPassword', ''],
        ];
    }

    #[DataProvider('missingDefaultProvider')]
    public function testMissingDefaultIsTheEmptyValueOfTheType(string $type, string $method, bool|float|int|string $expectedDefault): void
    {
        $this->repository->expects(self::once())
            ->method($method)
            ->with(self::GROUP, self::NAME, $expectedDefault)
            ->willReturn($expectedDefault);

        self::assertSame($expectedDefault, $this->extension->getConfigValue($type, self::GROUP, self::NAME));
    }

    public function testDefaultIsConvertedToTheRequestedType(): void
    {
        $this->repository->expects(self::once())
            ->method('getInt')
            ->with(self::GROUP, self::NAME, 25)
            ->willReturn(25);

        self::assertSame(25, $this->extension->getConfigValue(ConfigInterface::INT, self::GROUP, self::NAME, '25'));
    }

    public function testUnknownTypeReturnsNull(): void
    {
        self::assertNull($this->extension->getConfigValue('Unknown', self::GROUP, self::NAME, 'fallback'));
    }
}
