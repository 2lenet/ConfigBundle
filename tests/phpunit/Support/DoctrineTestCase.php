<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Tests\Support;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\ResolveTargetEntityListener;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;
use Lle\ConfigBundle\Contracts\ConfigInterface;
use Lle\ConfigBundle\Service\CacheManager;
use Lle\ConfigBundle\Tests\Fixtures\Config;
use Lle\ConfigBundle\Tests\Fixtures\ConfigRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * Real Doctrine on an in-memory SQLite database, with ConfigInterface resolved to the test entity.
 */
abstract class DoctrineTestCase extends TestCase
{
    protected const CONFIG_TABLE = 'config';
    protected const FIXTURES_DIRECTORY = __DIR__ . '/../Fixtures';

    protected EntityManager $entityManager;
    protected CacheManager $cacheManager;
    protected ConfigRepository $repository;

    protected function setUp(): void
    {
        $configuration = ORMSetup::createAttributeMetadataConfiguration([self::FIXTURES_DIRECTORY], true);
        // ORM 3 on PHP 8.4; ORM 2 (used by some projects) has no such method and generates its proxies itself
        if (method_exists($configuration, 'enableNativeLazyObjects')) { // @phpstan-ignore function.alreadyNarrowedType
            $configuration->enableNativeLazyObjects(true);
        }
        // same column names as a Symfony project (value_string, tenant_id…)
        $configuration->setNamingStrategy(new UnderscoreNamingStrategy(CASE_LOWER));

        $resolveTargetEntity = new ResolveTargetEntityListener();
        $resolveTargetEntity->addResolveTargetEntity(ConfigInterface::class, Config::class, []);
        $eventManager = new EventManager();
        $eventManager->addEventListener([Events::loadClassMetadata, Events::onClassMetadataNotFound], $resolveTargetEntity);

        $connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true], $configuration);
        $this->entityManager = new EntityManager($connection, $configuration, $eventManager);
        (new SchemaTool($this->entityManager))->createSchema($this->entityManager->getMetadataFactory()->getAllMetadata());

        $registry = $this->createMock(ManagerRegistry::class);
        $registry->method('getManagerForClass')->willReturn($this->entityManager);

        $this->cacheManager = new CacheManager(new ArrayAdapter());
        $this->repository = new ConfigRepository($this->cacheManager, $registry);
    }

    protected function saveConfig(Config $config): Config
    {
        $this->entityManager->persist($config);
        $this->entityManager->flush();

        return $config;
    }

    protected function countStoredConfigs(string $group, string $label): int
    {
        return (int) $this->entityManager->getConnection()->fetchOne(
            'SELECT COUNT(*) FROM ' . self::CONFIG_TABLE . ' WHERE "group" = ? AND label = ?',
            [$group, $label],
        );
    }
}
