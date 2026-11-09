<?php

declare(strict_types=1);

namespace Lle\ConfigBundle\Tests\EventListener;

use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Lle\ConfigBundle\EventListener\ConfigTableNameListener;
use Lle\ConfigBundle\Tests\Fixtures\Config;
use Lle\ConfigBundle\Tests\Support\DoctrineTestCase;

/**
 * The configuration entity of a project, declared without ORM\Table, is stored in "lle_config".
 */
class ConfigTableNameListenerTest extends DoctrineTestCase
{
    public function testTheConfigEntityIsMappedOnTheLleConfigTable(): void
    {
        self::assertSame('lle_config', $this->entityManager->getClassMetadata(Config::class)->getTableName());
    }

    public function testTheLleConfigTableIsCreatedBySchemaTool(): void
    {
        $schemaManager = $this->entityManager->getConnection()->createSchemaManager();

        self::assertTrue($schemaManager->tablesExist(['lle_config']));
        self::assertFalse($schemaManager->tablesExist(['config']));
    }

    public function testAnotherEntityKeepsItsTable(): void
    {
        $metadata = new ClassMetadata(\stdClass::class);
        $metadata->setPrimaryTable(['name' => 'other']);

        (new ConfigTableNameListener())->loadClassMetadata(new LoadClassMetadataEventArgs($metadata, $this->entityManager));

        self::assertSame('other', $metadata->getTableName());
    }
}
